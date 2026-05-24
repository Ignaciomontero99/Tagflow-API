<?php


namespace App\EventSubscriber;

use App\Entity\UserRefreshTokens;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTAuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof Users) {
            return;
        }

        if (method_exists($user, 'setLastLoginAt')) {
            $user->setLastLoginAt(new \DateTime());
        }

        $plainRefreshToken = bin2hex(random_bytes(64));
        $hashedRefreshToken = hash('sha256', $plainRefreshToken);

        $refreshToken = new UserRefreshTokens();
        $refreshToken->setUser($user);
        $refreshToken->setTokenHash($hashedRefreshToken);
        $refreshToken->setExpiresAt((new \DateTime())->modify('+7 days'));
        $refreshToken->setCreatedAt(new \DateTime());

        $this->em->persist($user);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $data = $event->getData();
        $data['refresh_token'] = $plainRefreshToken;

        $event->setData($data);
    }
}