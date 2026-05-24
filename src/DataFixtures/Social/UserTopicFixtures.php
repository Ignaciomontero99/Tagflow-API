<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\TagFixtures;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Tag;
use App\Entity\Tags;
use App\Entity\User;
use App\Entity\Users;
use App\Entity\UserTopic;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserTopicFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['user_topics', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, TagFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $pairs = [];

        for ($i = 1; $i <= 120; $i++) {
            $user = $this->getReference(FixtureRefs::USER . $i, Users::class);

            foreach ($this->faker->randomElements(range(1, 30), $this->faker->numberBetween(3, 8)) as $tagIndex) {
                $tag = $this->getReference(FixtureRefs::TAG . $tagIndex, Tags::class);

                $key = $i . '-' . $tagIndex;
                if (isset($pairs[$key])) {
                    continue;
                }
                $pairs[$key] = true;

                $userTopic = new UserTopic();
                $userTopic->setUser($user);
                $userTopic->setTag($tag);
                $userTopic->setCreatedAt($this->randomDateTime('-8 months', 'now'));

                $manager->persist($userTopic);
            }
        }

        $manager->flush();
    }
}
