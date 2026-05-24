<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Follow;
use App\Entity\Follows;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FollowFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['follows', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $pairs = [];

        for ($i = 0; $i < 950; $i++) {
            $follower = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);
            $followed = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            if ($follower === $followed) {
                continue;
            }

            $key = $follower->getUsername() . '->' . $followed->getUsername();
            if (isset($pairs[$key])) {
                continue;
            }
            $pairs[$key] = true;

            $follow = new Follows();
            $follow->setFollower($follower);
            $follow->setFollowed($followed);
            $follow->setCreatedAt($this->randomDateTime('-1 year', 'now'));

            $manager->persist($follow);
        }

        $manager->flush();
    }
}
