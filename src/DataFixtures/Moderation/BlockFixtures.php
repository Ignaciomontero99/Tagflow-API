<?php

namespace App\DataFixtures\Moderation;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Blocks;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BlockFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['blocks', 'moderation', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $pairs = [];

        for ($i = 0; $i < 140; $i++) {
            $user = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);
            $blocked = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            if ($user === $blocked) {
                continue;
            }

            $key = $user->getUsername() . '-' . $blocked->getUsername();
            if (isset($pairs[$key])) {
                continue;
            }
            $pairs[$key] = true;

            $block = new Blocks();
            $block->setUser($user);
            $block->setBlockedUser($blocked);
            $block->setReason($this->chance(45) ? $this->faker->sentence(8) : null);
            $block->setCreatedAt($this->randomDateTime('-5 months', 'now'));

            $manager->persist($block);
        }

        $manager->flush();
    }
}
