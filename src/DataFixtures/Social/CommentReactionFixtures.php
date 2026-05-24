<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Comment;
use App\Entity\CommentReaction;
use App\Entity\CommentReactions;
use App\Entity\Comments;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentReactionFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['comment_reactions', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [CommentFixtures::class, UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $used = [];
        $types = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

        for ($i = 0; $i < 1800; $i++) {
            $user = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);
            $comment = $this->getReference(FixtureRefs::COMMENT . $this->faker->numberBetween(1, 2500), Comments::class);

            $key = $user->getUsername() . '-' . spl_object_id($comment);
            if (isset($used[$key])) {
                continue;
            }
            $used[$key] = true;

            $reaction = new CommentReactions();
            $reaction->setUser($user);
            $reaction->setComment($comment);
            $reaction->setType($this->pick($types));
            $reaction->setCreatedAt($this->randomDateTime('-6 months', 'now'));

            $manager->persist($reaction);
            $comment->setReactionCount($comment->getReactionCount() + 1);
        }

        $manager->flush();
    }
}
