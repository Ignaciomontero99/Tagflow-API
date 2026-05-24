<?php

namespace App\DataFixtures\Moderation;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\PostReport;
use App\Entity\PostReports;
use App\Entity\Posts;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostReportFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['post_reports', 'moderation', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, \App\DataFixtures\Social\PostFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $reasons = ['spam', 'abuso', 'acoso', 'violencia', 'desinformacion', 'otro'];
        $statuses = ['open', 'reviewing', 'resolved', 'rejected'];

        for ($i = 0; $i < 120; $i++) {
            $reporter = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);
            $target = $this->getReference(FixtureRefs::POST . $this->faker->numberBetween(1, 700), Posts::class);

            $report = new PostReports();
            $report->setReporter($reporter);
            $report->setPost($target);
            $report->setReason($this->pick($reasons));
            $report->setDetails($this->chance(70) ? $this->faker->paragraph() : null);
            $report->setStatus($this->pick($statuses));
            $report->setCreatedAt($this->randomDateTime('-5 months', 'now'));
            $report->setResolvedAt(in_array($report->getStatus(), ['resolved', 'rejected'], true) ? $this->randomDateTime('-1 month', 'now') : null);

            $manager->persist($report);
        }

        $manager->flush();
    }
}
