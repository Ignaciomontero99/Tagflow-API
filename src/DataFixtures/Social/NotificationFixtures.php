<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Notification;
use App\Entity\Notifications;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NotificationFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['notifications', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, PostFixtures::class, CommentFixtures::class, \App\DataFixtures\Messaging\ConversationFixtures::class, \App\DataFixtures\Messaging\MessageFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $types = ['follow', 'post_reaction', 'comment', 'reply', 'message', 'mention', 'system'];
        $refTypes = ['post', 'comment', 'message', 'user', 'conversation', 'system'];

        for ($i = 0; $i < 1500; $i++) {
            $user = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            $notification = new Notifications();
            $notification->setUser($user);
            $notification->setSender($this->chance(85) ? $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class) : null);
            $notification->setType($this->pick($types));
            $notification->setTitle(ucfirst(str_replace('_', ' ', $notification->getType())));
            $notification->setMessage($this->faker->sentence(12));
            $notification->setReferenceType($this->pick($refTypes));
            $notification->setReferenceId($this->faker->numberBetween(1, 5000));
            $notification->setIsRead($this->chance(60));
            $notification->setReadAt(
                $notification->getIsRead()
                    ? $this->randomDateTime('-2 months', 'now')
                    : null
            );
            $notification->setCreatedAt($this->randomDateTime('-6 months', 'now'));

            $manager->persist($notification);
        }

        $manager->flush();
    }
}
