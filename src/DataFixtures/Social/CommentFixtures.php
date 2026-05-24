<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Comment;
use App\Entity\Comments;
use App\Entity\Post;
use App\Entity\Posts;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['comments', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [PostFixtures::class, UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $index = 1;

        for ($i = 0; $i < 1800; $i++) {
            $post = $this->getReference(FixtureRefs::POST . $this->faker->numberBetween(1, 700), Posts::class);
            $user = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            $comment = new Comments();
            $comment->setUser($user);
            $comment->setPost($post);
            $comment->setParentComment(null);
            $comment->setContent($this->faker->realTextBetween(25, 280));
            $comment->setIsEdited($this->chance(12));
            $comment->setReactionCount(0);
            $comment->setReplyCount(0);
            $comment->setCreatedAt($this->randomDateTime('-8 months', 'now'));
            $comment->setUpdatedAt($this->randomDateTime('-3 months', 'now'));
            $comment->setDeletedAt(null);

            $manager->persist($comment);
            $this->addReference(FixtureRefs::COMMENT . $index++, $comment);
            $post->setCommentCount($post->getCommentCount() + 1);
        }

        for ($i = 0; $i < 700; $i++) {
            $parent = $this->getReference(FixtureRefs::COMMENT . $this->faker->numberBetween(1, 1800), Comments::class);
            $user = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            $reply = new Comments();
            $reply->setUser($user);
            $reply->setPost($parent->getPost());
            $reply->setParentComment($parent);
            $reply->setContent($this->faker->realTextBetween(20, 220));
            $reply->setIsEdited($this->chance(8));
            $reply->setReactionCount(0);
            $reply->setReplyCount(0);
            $reply->setCreatedAt($this->randomDateTime('-6 months', 'now'));
            $reply->setUpdatedAt($this->randomDateTime('-3 months', 'now'));
            $reply->setDeletedAt(null);

            $manager->persist($reply);
            $this->addReference(FixtureRefs::COMMENT . $index++, $reply);

            $parent->setReplyCount($parent->getReplyCount() + 1);
            $parent->getPost()->setCommentCount($parent->getPost()->getCommentCount() + 1);
        }

        $manager->flush();
    }
}
