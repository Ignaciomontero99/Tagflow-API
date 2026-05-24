<?php

namespace App\DataFixtures\Security;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\UserRefreshTokens;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserRefreshTokenFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['user_refresh_tokens', 'security', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->faker->randomElements(range(1, 120), 55) as $userId) {
            $user = $this->getReference(FixtureRefs::USER . $userId, Users::class);

            for ($i = 0; $i < $this->faker->numberBetween(1, 3); $i++) {
                $token = new UserRefreshTokens();
                $token->setUser($user);
                $token->setTokenHash($this->tokenHash(32));
                $token->setExpiresAt($this->randomDateTime('now', '+30 days'));
                $token->setRevokedAt($this->chance(20) ? $this->randomDateTime('-15 days', 'now') : null);
                $token->setCreatedAt($this->randomDateTime('-30 days', 'now'));
                $token->setLastUsedAt($this->chance(65) ? $this->randomDateTime('-10 days', 'now') : null);
                $token->setIpAddress($this->faker->ipv4());
                $token->setUserAgent($this->faker->userAgent());

                $manager->persist($token);
            }
        }

        $manager->flush();
    }
}
