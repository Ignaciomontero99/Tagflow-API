<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Post;
use App\Entity\PostReaction;
use App\Entity\PostReactions;
use App\Entity\Posts;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostReactionFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['post_reactions', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [PostFixtures::class, UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $used = [];
        $types = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

        for ($i = 0; $i < 3000; $i++) {
            $user = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);
            $post = $this->getReference(FixtureRefs::POST . $this->faker->numberBetween(1, 700), Posts::class);

            $key = $user->getUsername() . '-' . spl_object_id($post);
            if (isset($used[$key])) {
                continue;
            }
            $used[$key] = true;

            $reaction = new PostReactions();
            $reaction->setUser($user);
            $reaction->setPost($post);
            $reaction->setType($this->pick($types));
            $reaction->setCreatedAt($this->randomDateTime('-7 months', 'now'));

            $manager->persist($reaction);
            $post->setReactionCount($post->getReactionCount() + 1);
        }

        $manager->flush();
    }
}
