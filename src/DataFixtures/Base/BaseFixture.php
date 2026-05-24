<?php

namespace App\DataFixtures\Base;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Exception;
use Faker\Factory;
use Faker\Generator;

abstract class BaseFixture extends Fixture
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('es_ES');
    }

    /**
     * @throws Exception
     */
    protected function randomDateTime($start = '-1 year', $end = 'now'): ?\DateTime
    {
        return new \DateTime(
            $this->faker->dateTimeBetween($start, $end)->format('Y-m-d H:i:s')
        );
    }

    protected function chance(int $percentage): bool
    {
        return $this->faker->numberBetween(1, 100) <= $percentage;
    }

    protected function pick(array $values): string
    {
        return $this->faker->randomElement($values);
    }

    protected function tokenHash(int $bytes = 32): string
    {
        return hash('sha256', bin2hex(random_bytes($bytes)));
    }
}
