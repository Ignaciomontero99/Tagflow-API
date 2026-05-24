<?php

namespace App\DataFixtures\Security;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\PasswordReset;
use App\Entity\PasswordResets;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PasswordResetFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['password_resets', 'security', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->faker->randomElements(range(1, 120), 35) as $userId) {
            $user = $this->getReference(FixtureRefs::USER . $userId, Users::class);

            $reset = new PasswordResets();
            $reset->setUser($user);
            $reset->setTokenHash($this->tokenHash(24));
            $reset->setExpiresAt($this->randomDateTime('now', '+2 days'));
            $reset->setUsedAt($this->chance(35) ? $this->randomDateTime('-8 days', 'now') : null);
            $reset->setCreatedAt($this->randomDateTime('-10 days', 'now'));

            $manager->persist($reset);
        }

        $manager->flush();
    }
}
