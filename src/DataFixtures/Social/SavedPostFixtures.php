<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Post;
use App\Entity\Posts;
use App\Entity\SavedPost;
use App\Entity\SavedPosts;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SavedPostFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['saved_posts', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [PostFixtures::class, UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $used = [];

        for ($i = 0; $i < 1200; $i++) {
            $user = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);
            $post = $this->getReference(FixtureRefs::POST . $this->faker->numberBetween(1, 700), Posts::class);

            $key = $user->getUsername() . '-' . spl_object_id($post);
            if (isset($used[$key])) {
                continue;
            }
            $used[$key] = true;

            $saved = new SavedPosts();
            $saved->setUser($user);
            $saved->setPost($post);
            if (method_exists($saved, 'setSavedAt')) {
                $saved->setSavedAt($this->randomDateTime('-6 months', 'now'));
            } elseif (method_exists($saved, 'setCreatedAt')) {
                $saved->setCreatedAt($this->randomDateTime('-6 months', 'now'));
            }

            $manager->persist($saved);
            $post->setSaveCount($post->getSaveCount() + 1);
        }

        $manager->flush();
    }
}
