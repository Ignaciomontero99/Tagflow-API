<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\TagFixtures;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Post;
use App\Entity\Posts;
use App\Entity\Tag;
use App\Entity\Tags;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['posts', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, TagFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $visibilities = ['public', 'followers', 'private'];
        $statuses = ['published', 'published', 'published', 'draft', 'archived'];

        for ($i = 1; $i <= 700; $i++) {
            $author = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            $post = new Posts();
            $post->setUser($author);
            $post->setContent($this->faker->realTextBetween(100, 900));
            $post->setVisibility($this->pick($visibilities));
            $post->setStatus($this->pick($statuses));
            $post->setCommentsEnabled(!$this->chance(8));
            $post->setIsAd($this->chance(5));
            $post->setLocationName($this->chance(25) ? $this->faker->city() : null);
            $post->setReactionCount(0);
            $post->setCommentCount(0);
            $post->setSaveCount(0);
            $post->setCreatedAt($this->randomDateTime('-10 months', 'now'));
            $post->setUpdatedAt($this->randomDateTime('-2 months', 'now'));
            $post->setDeletedAt(null);

            if (method_exists($post, 'addTag')) {
                foreach ($this->faker->randomElements(range(1, 30), $this->faker->numberBetween(1, 4)) as $tagIndex) {
                    $tag = $this->getReference(FixtureRefs::TAG . $tagIndex, Tags::class);
                    $post->addTag($tag);
                }
            }

            $manager->persist($post);
            $this->addReference(FixtureRefs::POST . $i, $post);
        }

        $manager->flush();
    }
}
