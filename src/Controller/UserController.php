<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Blocks;
use App\Entity\Follows;
use App\Entity\PostMedia;
use App\Entity\Posts;
use App\Entity\SavedPosts;
use App\Entity\Tags;
use App\Entity\Users;
use App\Entity\UserTopic;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{

    public function perfilUser (
        Request $request,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $idUser = $request->get('id_user');

        if (!$idUser) {
            return $this->json(['message'=>'No se ha encontrado el usuario con el id: '. $idUser . ' en el servidor de la base de datos.'], 404);
        }

        if ($request->isMethod('GET')){
            $user = $this->getDoctrine()
                ->getRepository(Users::class)
                ->findOneBy(['id' => $idUser]);

            if (!$user) {
                return $this->json(['message'=>'No se ha encontrado el usuario con el id: '. $idUser . ' en el servidor de la base de datos.'], 404);
            }

            return $this->json([
                'id' => $user->getId(),
                'username' => $user->getUsernameUser(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'bio' => $user->getBio(),
                'profileImageUrl' => $user->getProfileImageUrl(),
                'websiteUrl' => $user->getWebsiteUrl(),
                'birthDate' => $user->getBirthDate(),
                'roles' => $user->getRoles(),
                'isVerified' => $user->getIsVerified(),
                'createdAt' => $user->getCreatedAt(),
            ]);
        }

        return $this->json(['message'=>'Usuario mostrado']);
    }

    public function updateProfile(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user){
            return $this->json([
                'message'=>'No autenticado.',
                401
            ]);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data){
            return $this->json([
               'message'=> 'JSON invalido.',
                400
           ]);
        }

        if ($request->isMethod('PATCH')){
            if (isset($data['name'])) {
                $user->setName($data['name']);
            }

            if (isset($data['bio'])) {
                $user->setBio($data['bio']);
            }

            if (isset($data['websiteUrl'])) {
                $user->setWebsiteUrl($data['websiteUrl']);
            }

            if (isset($data['profileImageUrl'])) {
                $user->setProfileImageUrl($data['profileImageUrl']);
            }

            if (isset($data['privacyLevel'])) {
                $user->setPrivacyLevel($data['privacyLevel']);
            }

            if (isset($data['birthDate'])) {
                $user->setBirthDate($data['birthDate'] ? new \DateTime($data['birthDate']) : null);
            }

            $user->setUpdatedAt(new \DateTime());

            $em->persist($user);
            $em->flush();
        }

        return $this->json(['message'=>'Perfil modificado']);
    }

    public function interests(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json([
                'message'=>'No autenticado.',
                401
            ]);
        }

        $userTopics = $em->getRepository(UserTopic::class)
        ->findBy(['user' => $user]);

        $interests = [];
        if ($request->isMethod('GET')){
            foreach ($userTopics as $userTopic) {
                $tag = $userTopic->getTag();

                $interests[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug(),
                    'created_at' => $tag->getCreatedAt()
                ];
            }

            return $this->json([
                'intereses del usuario' => $interests
            ]);
        }

        if ($request->isMethod('PUT')){
            $data = json_decode($request->getContent(), true);

            if (!$data){
                return $this->json([
                    'message'=> 'JSON invalido.',
                    400
                ]);
            }

            $interestIds = array_unique($data['interests_ids']);

            if (!$interestIds){
                return $this->json(['message'=>'Debes enviar como array los ids.'], 400);
            }

            foreach ($interestIds as $interestId) {
                $tag = $em->getRepository(Tags::class)
                    ->find($interestId);

                if (!$tag) {
                    return $this->json([
                        'message' => 'Uno de los intereses enviados no existe.',
                        'interest_id' => $interestId
                    ], 404);
                }

                $tagsExisting = $em->getRepository(UserTopic::class)
                    ->findOneBy([
                        'user' => $user,
                        'tag' => $tag,
                    ]);

                if ($tagsExisting) {
                    $em->remove($tagsExisting);
                } else {
                    $userTopicNew = new UserTopic();
                    $userTopicNew->setUser($user);
                    $userTopicNew->setTag($tag);
                    $userTopicNew->setCreatedAt(new \DateTime());

                    $em->persist($userTopicNew);
                }
            }

            $em->persist($user);
            $em->flush();

            return $this -> json(['message' => 'Se registran con los datos del usuario.']);

        }

        return $this->json(['message'=>'Intereses del usuario.']);
    }

    public function following(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user){
            return $this->json([
                'message'=>'No autenticado.',
                401
            ]);
        }

        $id = $request->get('id_user');
        
        if (!$id){
            return $this->json([
                'message' => ['No se ha encontrado el usuario.'],
                ]);
        }

        $followers = $em->getRepository(Follows::class)
            ->findBy(['follower' => $user]);

        if (!$followers) {
            return $this->json([
                'message' => 'No se encontran datos de los usuarios. No se encontran datos de los usuarios.',
            ]);
        }

        if ($request->isMethod('GET')) {
            $followersList = [];
            foreach ($followers as $follow) {
                $followedUser = $follow->getFollowed();
                $followersList[] = [
                    'id' => $followedUser->getId(),
                    'username' => $followedUser->getUsernameUser(),
                    'name' => $followedUser->getName(),
                    'profileImageUrl' => $followedUser->getProfileImageUrl(),
                    'bio' => $followedUser->getBio(),
                    'isVerified' => $followedUser->getIsVerified(),
                ];
            }

            return $this->json([
                'Count' => count($followers),
                'following' => $followersList
            ]);
        }

        return $this->json(['message' => 'Lista de followers del usuario.']);
    }

    public function followers(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
                401
            ]);
        }

        $id = $request->get('id_user');

        if (!$id) {
            return $this->json([
                'message' => ['No se ha encontrado el usuario.'],
            ]);
        }

        $followers = $em->getRepository(Follows::class)
            ->findBy(['followed' => $user]);

        if (!$followers) {
            return $this->json([
                'message' => 'No se encontran datos de los usuarios.',
            ]);
        }

        if ($request->isMethod('GET')) {
            $followersList = [];
            foreach ($followers as $follow) {
                $followerUser = $follow->getFollower();
                $followersList[] = [
                    'id' => $followerUser->getId(),
                    'username' => $followerUser->getUsernameUser(),
                    'name' => $followerUser->getName(),
                    'profileImageUrl' => $followerUser->getProfileImageUrl(),
                    'bio' => $followerUser->getBio(),
                    'isVerified' => $followerUser->getIsVerified(),
                ];
            }

            return $this->json([
                'Count' => count($followers),
                'followers' => $followersList
            ]);
        }

        return $this->json(['message' => 'Lista de followers del usuario.']);
    }

    public function follow(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $me = $this->getUser();
        if (!$me) {
            return $this->json([
                'message' => 'No autenticado.',
                401
            ]);
        }

        $id = $request->get('id_user');
        if (!$id) {
            return $this->json([
                'message' => ['No se ha recibido el $id'],
                404
            ]);
        }

        if ($me->getId() == $id) {
            return $this->json([
                'message' => 'No puedes seguirte a ti mismo.',
                400
            ]);
        }

        $user = $em->getRepository(Users::class)
            ->findOneBy(['id' => $id]);

        if (!$user) {
            return $this->json([
                'message' => 'No se ha encontrado el usuario. No se encontra el registro de los usuarios.'
            ]);
        }

        $follows = $em->getRepository(Follows::class)
            ->findOneBy([
                'follower' => $me,
                'followed' => $user
            ]);

        if ($request->isMethod('POST')) {
            if ($follows) {
                return $this->json([
                    'message' => 'Ya sigues a este usuario.',
                    'is_following' => true
                ], 400);
            }

            $follow = new Follows();
            $follow->setFollower($me);
            $follow->setFollowed($user);
            $follow->setCreatedAt(new \DateTime());

            $em->persist($follow);
            $em->flush();

            return $this->json([
                'message' => 'Sigues al usuario.',
                'is_following' => true
            ]);
        }
        if ($request->isMethod('DELETE')) {
            if (!$follows) {
                return $this->json([
                    'message' => 'No sigues a este usuario',
                    'is_following' => false
                ], 404);
            }

            $em->remove($follows);
            $em->flush();

            return $this->json([
                'message' => 'Se ha dejado de seguir al usuario',
                'is_following' => false
            ]);
        }

        // Si es un GET u otro método, devolver el estado actual
        return $this->json([
            'message' => 'Estado de seguimiento del usuario.',
            'is_following' => $follows !== null
        ]);
    }

    public function block(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $me = $this->getUser();

        if (!$me instanceof Users) {
            return $this->json([
                'message' => 'No autenticado.'
            ], 401);
        }

        $id = $request->get('id_user');

        if (!$id) {
            return $this->json([
                'message' => 'No se ha recibido el id del usuario.'
            ], 400);
        }

        if ($me->getId() == $id) {
            return $this->json([
                'message' => 'No puedes bloquearte a ti mismo.'
            ], 400);
        }

        $user = $em->getRepository(Users::class)->find($id);

        if (!$user) {
            return $this->json([
                'message' => 'No se ha encontrado el usuario.'
            ], 404);
        }

        $block = $em->getRepository(Blocks::class)->findOneBy([
            'user' => $me,
            'blockedUser' => $user
        ]);

        if ($request->isMethod('POST')) {
            if ($block) {
                return $this->json([
                    'message' => 'Este usuario ya está bloqueado.'
                ]);
            }

            $block = new Blocks();
            $block->setUser($me);
            $block->setBlockedUser($user);
            $block->setCreatedAt(new \DateTime());

            $em->persist($block);
            $em->flush();

            return $this->json([
                'message' => 'Usuario bloqueado correctamente.'
            ], 201);
        }

        if ($request->isMethod('DELETE')) {
            if (!$block) {
                return $this->json([
                    'message' => 'Este usuario no estaba bloqueado.'
                ], 404);
            }

            $em->remove($block);
            $em->flush();

            return $this->json([
                'message' => 'Usuario desbloqueado correctamente.'
            ]);
        }

        return $this->json([
            'message' => 'Método no permitido.'
        ], 405);
    }

    public function postsUser(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse
    {

        $id_user = $request->get('id_user');
        $user = $em->getRepository(Users::class)
            ->find($id_user);

        if (!$user) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if ($request->isMethod('GET')) {
            $posts = $em->getRepository(Posts::class)
                ->findBy(['user' => $user], ['createdAt' => 'DESC']);

            $postsList = [];
            foreach ($posts as $post) {
                $isSaved = false;
                $currentUser = $this->getUser();
                if ($currentUser) {
                    $isSaved = $em->getRepository(SavedPosts::class)
                            ->findOneBy([
                                'user' => $currentUser,
                                'post' => $post
                            ]) !== null;
                }

                $postsList[] = [
                    'id' => $post->getId(),
                    'content' => $post->getContent(),
                    'created_at' => $post->getCreatedAt()
                        ? $post->getCreatedAt()->format('Y-m-d H:i:s')
                        : null,
                    'is_saved' => $isSaved,
                    'reactions_count' => $post->getReactionCount(),
                    'comments_count' => $post->getCommentCount(),
                    'user' => [
                        'id' => $user->getId(),
                        'username' => $user->getUsernameUser(),
                        'profileImageUrl' => $user->getProfileImageUrl()
                    ]
                ];
            }

            return $this->json([
                'count' => count($posts),
                'posts' => $postsList
            ]);
        }
    
        return $this->json([
            'message' => 'Método no permitido.'
        ], 405);
    }

    public function passwordChange(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof Users) {
            return $this->json([
                'message' => 'No autenticado.'
            ], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'message' => 'JSON inválido.'
            ], 400);
        }

        $currentPassword = isset($data['currentPassword'])
            ? (string) $data['currentPassword']
            : null;

        $newPassword = isset($data['newPassword'])
            ? (string) $data['newPassword']
            : null;

        $confirmPassword = isset($data['confirmPassword'])
            ? (string) $data['confirmPassword']
            : null;

        if (!$currentPassword || !$newPassword) {
            return $this->json([
                'message' => 'Se requieren currentPassword y newPassword.'
            ], 400);
        }

        if (!$passwordEncoder->isPasswordValid($user, $currentPassword)) {
            return $this->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 400);
        }

        if (strlen($newPassword) < 8) {
            return $this->json([
                'message' => 'La nueva contraseña debe tener al menos 8 caracteres.'
            ], 400);
        }

        if ($confirmPassword !== null && $newPassword !== $confirmPassword) {
            return $this->json([
                'message' => 'La confirmación de contraseña no coincide.'
            ], 400);
        }

        if ($passwordEncoder->isPasswordValid($user, $newPassword)) {
            return $this->json([
                'message' => 'La nueva contraseña no puede ser igual a la contraseña actual.'
            ], 400);
        }

        $hashedPassword = $passwordEncoder->encodePassword($user, $newPassword);

        $user->setPassword($hashedPassword);

        if (method_exists($user, 'setUpdatedAt')) {
            $user->setUpdatedAt(new \DateTime());
        }

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Contraseña actualizada correctamente.'
        ], 200);
    }

    public function tagList(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.'
            ], 401);
        }

        if ($request->isMethod('GET')) {
            $userTopics = $em->getRepository(UserTopic::class)
                ->findBy(['user' => $user]);

            $tagsList = [];
            foreach ($userTopics as $userTopic) {
                $tag = $userTopic->getTag();

                $tagsList[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug(),
                    'created_at' => $tag->getCreatedAt()
                        ? $tag->getCreatedAt()->format('Y-m-d H:i:s')
                        : null
                ];
            }

            return $this->json([
                'count' => count($tagsList),
                'tags' => $tagsList
            ]);
        }
    
        return $this->json([
            'message' => 'Método no permitido.'
        ], 405);
    }

}