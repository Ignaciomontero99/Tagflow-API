<?php

namespace App\DataFixtures\Security;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\LoginAttempt;
use App\Entity\LoginAttempts;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class LoginAttemptFixtures extends BaseFixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['login_attempts', 'security', 'all'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 280; $i++) {
            $attempt = new LoginAttempts();
            $attempt->setEmail($this->faker->safeEmail());
            $attempt->setIpAddress($this->faker->ipv4());
            $attempt->setSuccess($this->chance(72));
            $attempt->setAttemptedAt($this->randomDateTime('-20 days', 'now'));

            $manager->persist($attempt);
        }

        $manager->flush();
    }
}
