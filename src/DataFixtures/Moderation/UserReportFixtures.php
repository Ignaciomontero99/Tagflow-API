<?php

namespace App\DataFixtures\Moderation;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\User;
use App\Entity\UserReport;
use App\Entity\UserReports;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserReportFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['user_reports', 'moderation', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $reasons = ['spam', 'suplantacion', 'acoso', 'contenido inapropiado', 'scam', 'otro'];
        $statuses = ['open', 'reviewing', 'resolved', 'rejected'];

        for ($i = 0; $i < 70; $i++) {
            $reporter = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);
            $reported = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            if ($reporter === $reported) {
                continue;
            }

            $report = new UserReports();
            $report->setReporter($reporter);
            $report->setReportedUser($reported);
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
