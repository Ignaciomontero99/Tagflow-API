<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PasswordResets;
use App\Entity\Posts;
use App\Entity\UserRefreshTokens;
use App\Entity\Users;
use App\Entity\UserTopic;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController
{

    public function register(
        Request                      $request,
        EntityManagerInterface       $em,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTTokenManagerInterface     $jwtManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'JSON inválido'], 400);
        }

        $username = isset($data['username']) ? trim($data['username']) : null;
        $email = isset($data['email']) ? trim($data['email']) : null;
        $password = isset($data['password']) ? (string) $data['password'] : null;

        if (!$username || !$email || !$password) {
            return $this->json([
                'message' => 'username, email y password son obligatorios'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'message' => 'El email no tiene un formato válido'
            ], 400);
        }

        if (strlen($password) < 6) {
            return $this->json([
                'message' => 'La contraseña debe tener al menos 6 caracteres'
            ], 400);
        }

        $existingByEmail = $em
            ->getRepository(Users::class)
            ->findOneBy(['email' => $email]);

        if ($existingByEmail) {
            return $this->json(['message' => 'El email ya existe'], 409);
        }

        $existingByUsername = $em
            ->getRepository(Users::class)
            ->findOneBy(['username' => $username]);

        if ($existingByUsername) {
            return $this->json(['message' => 'El username ya existe'], 409);
        }

        $user = new Users();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRole('user');

        $hash = $passwordEncoder->encodePassword($user, $password);
        $user->setPassword($hash);

        if (method_exists($user, 'setCreatedAt')) {
            $user->setCreatedAt(new \DateTime());
        }

        if (method_exists($user, 'setUpdatedAt')) {
            $user->setUpdatedAt(new \DateTime());
        }

        if (method_exists($user, 'setIsActive')) {
            $user->setIsActive(true);
        }

        if (method_exists($user, 'setIsVerified')) {
            $user->setIsVerified(false);
        }

        if (method_exists($user, 'setAccountStatus')) {
            $user->setAccountStatus('active');
        }

        if (method_exists($user, 'setPrivacyLevel')) {
            $user->setPrivacyLevel('public');
        }

        $em->persist($user);
        $em->flush();

        $token = $jwtManager->create($user);

        $refreshToken = bin2hex(random_bytes(64));

        $refreshTokenEntity = new UserRefreshTokens();
        $refreshTokenEntity->setUser($user);
        $refreshTokenEntity->setTokenHash($refreshToken);
        $refreshTokenEntity->setCreatedAt(new \DateTime());
        $refreshTokenEntity->setExpiresAt((new \DateTime())->modify('+30 days'));

        if (method_exists($refreshTokenEntity, 'setRevokedAt')) {
            $refreshTokenEntity->setRevokedAt(null);
        }

        $em->persist($refreshTokenEntity);
        $em->flush();

        return $this->json([
            'message' => 'Usuario registrado correctamente',
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => method_exists($user, 'getId') ? $user->getId() : null,
                'username' => method_exists($user, 'getUsername') ? $user->getUsername() : null,
                'email' => method_exists($user, 'getEmail') ? $user->getEmail() : null,
                'roles' => $user->getRoles(),
            ]
        ], 201);
    }

    public function login()
    {
        throw new \Exception('Este endpoint lo gestiona el firewall de Symfony.');
    }

    public function refresh(
        Request $request,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return $this->json(['message' => 'Refresh token requerido'], 400);
        }

        $refreshTokenHash = hash('sha256', $refreshToken);

        $tokenEntity = $em->getRepository(UserRefreshTokens::class)
            ->findOneBy(['tokenHash' => $refreshTokenHash]);

        if (!$tokenEntity) {
            return $this->json(['message' => 'Refresh token inválido o no existe'], 401);
        }

        if ($tokenEntity->getRevokedAt() !== null) {
            return $this->json(['message' => 'Refresh token revocado'], 401);
        }

        if ($tokenEntity->getExpiresAt() < new \DateTime()) {
            return $this->json(['message' => 'Refresh token expirado'], 401);
        }

        $user = $tokenEntity->getUser();

        if (!$user) {
            return $this->json(['message' => 'Usuario asociado no encontrado'], 401);
        }

        $newJwt = $jwtManager->create($user);

        $newRefreshToken = bin2hex(random_bytes(64));
        $newRefreshTokenHash = hash('sha256', $newRefreshToken);

        $tokenEntity->setTokenHash($newRefreshTokenHash);
        $tokenEntity->setExpiresAt((new \DateTime())->modify('+30 days'));

        if (method_exists($tokenEntity, 'setLastUsedAt')) {
            $tokenEntity->setLastUsedAt(new \DateTime());
        }

        if (method_exists($tokenEntity, 'setIpAddress')) {
            $tokenEntity->setIpAddress($request->getClientIp());
        }

        if (method_exists($tokenEntity, 'setUserAgent')) {
            $tokenEntity->setUserAgent($request->headers->get('User-Agent'));
        }

        $em->flush();

        return $this->json([
            'token' => $newJwt,
            'refresh_token' => $newRefreshToken
        ]);
    }

    public function me(Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        return $this->json([
            'id'=> $user->getId(),
            'username' => $user->getUsernameUser(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    public function logout(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $refreshToken = $data['refresh_token'] ?? null;

        if ($refreshToken) {
            $repo = $em->getRepository(UserRefreshTokens::class);
            $token = $repo->findOneBy(['tokenHash' => $refreshToken]);

            if ($token) {
                $em->remove($token);
                $em->flush();
            }
        }

        return $this->json(['message' => 'Logout correcto']);
    }

    public function logoutAll(
        Security $security,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $security->getUser();

        $repo = $em->getRepository(UserRefreshTokens::class);

        $tokens = $repo->findBy(['user' => $user]);

        foreach ($tokens as $token) {
            $em->remove($token);
        }

        $em->flush();

        return $this->json(['message' => 'Logout en todos los dispositivos']);
    }

    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = isset($data['email']) ? trim($data['email']) : null;

        if (!$email) {
            return $this->json([
                'message' => 'Email requerido'
            ], 400);
        }

        $user = $em->getRepository(Users::class)->findOneBy([
            'email' => $email
        ]);

        if (!$user) {
            return $this->json([
                'message' => 'Si el email existe, recibirás instrucciones'
            ], 200);
        }

        $plainToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $plainToken);

        $reset = new PasswordResets();

        if (method_exists($reset, 'setUser')) {
            $reset->setUser($user);
        }

        if (method_exists($reset, 'setTokenHash')) {
            $reset->setTokenHash($hashedToken);
        }

        if (method_exists($reset, 'setExpiresAt')) {
            $reset->setExpiresAt((new \DateTime())->modify('+1 hour'));
        }

        if (method_exists($reset, 'setUsedAt')) {
            $reset->setUsedAt(null);
        }

        if (method_exists($reset, 'setCreatedAt')) {
            $reset->setCreatedAt(new \DateTime());
        }

        $em->persist($reset);
        $em->flush();

        return $this->json([
            'message' => 'Email enviado',
            'token_forgot' => $plainToken
        ], 200);
    }

    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return $this->json(['message' => 'Datos inválidos'], 400);
        }

        $hashedToken = hash('sha256', $token);

        $repo = $em->getRepository(PasswordResets::class);
        $reset = $repo->findOneBy(['tokenHash' => $hashedToken]);

        if (!$reset || $reset->getExpiresAt() < new \DateTime()) {
            return $this->json(['message' => 'Token inválido'], 400);
        }

        $user = $reset->getUser();

        $hash = $encoder->encodePassword($user, $newPassword);

        if (method_exists($user, 'setPassword')) {
            $user->setPassword($hash);
        } elseif (method_exists($user, 'setPasswordHash')) {
            $user->setPasswordHash($hash);
        }

        $em->remove($reset);
        $em->flush();

        return $this->json(['message' => 'Password actualizada']);
    }

    public function deleteAccount(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        if (!$request->isMethod('DELETE')) {
            return $this->json(['message' => 'Método no permitido'], 405);
        }

        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $postRepo = $em->getRepository(Posts::class);
        $posts = $postRepo->findBy(['user' => $user]);

        foreach ($posts as $post) {
            $em->remove($post);
        }

        $refreshTokenRepo = $em->getRepository(UserRefreshTokens::class);
        $refreshTokens = $refreshTokenRepo->findBy(['user' => $user]);

        foreach ($refreshTokens as $token) {
            $em->remove($token);
        }

        $passwordResetRepo = $em->getRepository(PasswordResets::class);
        $passwordResets = $passwordResetRepo->findBy(['user' => $user]);

        foreach ($passwordResets as $reset) {
            $em->remove($reset);
        }

        $userTopicRepo = $em->getRepository(UserTopic::class);
        $userTopics = $userTopicRepo->findBy(['user' => $user]);

        foreach ($userTopics as $userTopic) {
            $em->remove($userTopic);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Cuenta eliminada correctamente'], 200);
    }


    /**
     * @Route("/test-auth-header", name="test_auth_header", methods={"GET"})
     */
    public function testAuthHeader(Request $request): JsonResponse
    {
        return $this->json([
            'authorization_header' => $request->headers->get('Authorization'),
            'http_authorization' => $request->server->get('HTTP_AUTHORIZATION'),
            'all_headers' => $request->headers->all(),
        ]);
    }
}
