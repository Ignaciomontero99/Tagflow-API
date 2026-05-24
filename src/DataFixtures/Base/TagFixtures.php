<?php

namespace App\DataFixtures\Base;

use App\DataFixtures\FixtureRefs;
use App\Entity\Tag;
use App\Entity\Tags;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends BaseFixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['tags', 'base', 'all'];
    }

    public function load(ObjectManager $manager): void
    {
        $names = [
            'tecnologia', 'gaming', 'programacion', 'frontend', 'backend',
            'php', 'symfony', 'javascript', 'react', 'angular',
            'docker', 'mysql', 'ia', 'devops', 'cine',
            'musica', 'viajes', 'deporte', 'fitness', 'fotografia',
            'api-rest', 'jwt', 'seguridad', 'testing', 'ui-ux',
            'mobile', 'cloud', 'linux', 'sql', 'arquitectura',
        ];

        foreach ($names as $i => $name) {
            $tag = new Tags();
            $tag->setName($name);
            $tag->setSlug($name);
            if (method_exists($tag, 'setParent')) {
                $tag->setParent(null);
            } elseif (method_exists($tag, 'setParentId')) {
                $tag->setParentId(null);
            }
            $tag->setUsageCount($this->faker->numberBetween(20, 500));
            $tag->setCreatedAt($this->randomDateTime('-2 years', '-8 months'));
            $tag->setUpdatedAt($this->randomDateTime('-3 months', 'now'));

            $manager->persist($tag);
            $this->addReference(FixtureRefs::TAG . ($i + 1), $tag);
        }

        $manager->flush();
    }
}
