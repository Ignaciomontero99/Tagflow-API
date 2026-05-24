<?php

namespace App\DataFixtures\Social;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\FixtureRefs;
use App\Entity\Post;
use App\Entity\PostMedia;
use App\Entity\Posts;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostMediaFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['post_media', 'social', 'all'];
    }

    public function getDependencies(): array
    {
        return [PostFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 700; $i++) {
            $post = $this->getReference(FixtureRefs::POST . $i, Posts::class);
            $mediaCount = $this->faker->numberBetween(0, 3);

            for ($sort = 1; $sort <= $mediaCount; $sort++) {
                $type = $this->pick(['image', 'image', 'image', 'video', 'gif']);

                $media = new PostMedia();
                $media->setPost($post);
                $media->setMediaUrl($type === 'video'
                    ? 'https://samplelib.com/lib/preview/mp4/sample-5s.mp4'
                    : $this->faker->imageUrl(1280, 720, 'nature', true)
                );
                $media->setMediaType($type);
                $media->setMimeType($type === 'video' ? 'video/mp4' : ($type === 'gif' ? 'image/gif' : 'image/jpeg'));
                $media->setSortOrder($sort);
                $media->setWidth($type === 'video' ? 1920 : 1280);
                $media->setHeight($type === 'video' ? 1080 : 720);
                $media->setDurationSeconds($type === 'video' ? $this->faker->numberBetween(4, 120) : null);
                $media->setCreatedAt($this->randomDateTime('-8 months', 'now'));

                $manager->persist($media);
            }
        }

        $manager->flush();
    }
}
