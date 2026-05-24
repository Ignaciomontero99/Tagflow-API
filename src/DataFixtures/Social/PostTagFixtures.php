<?php

namespace App\DataFixtures\Social;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;

class PostTagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /** @var Connection $conn */
        $conn = $manager->getConnection();

        // Obtener posts
        $posts = $conn->fetchAllAssociative("SELECT id FROM posts");

        // Obtener tags
        $tags = $conn->fetchAllAssociative("SELECT id FROM tags");

        if (!$posts || !$tags) {
            return;
        }

        foreach ($posts as $post) {

            // 1 a 3 tags por post
            $randomKeys = array_rand($tags, min(3, count($tags)));

            if (!is_array($randomKeys)) {
                $randomKeys = [$randomKeys];
            }

            foreach ($randomKeys as $key) {

                $tag = $tags[$key];

                // evitar duplicados
                $exists = $conn->fetchOne("
                    SELECT COUNT(*) 
                    FROM post_tag 
                    WHERE post_id = :post AND tag_id = :tag
                ", [
                    'post' => $post['id'],
                    'tag' => $tag['id']
                ]);

                if ($exists) {
                    continue;
                }

                $conn->insert('post_tag', [
                    'post_id' => $post['id'],
                    'tag_id' => $tag['id']
                ]);
            }
        }
    }
}