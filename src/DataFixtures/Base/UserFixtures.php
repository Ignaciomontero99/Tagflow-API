<?php

namespace App\DataFixtures\Base;

use App\DataFixtures\FixtureRefs;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture implements FixtureGroupInterface
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct();
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getGroups(): array
    {
        return ['users', 'base', 'all'];
    }

    public function load(ObjectManager $manager): void
    {
        $roles = ['user', 'moderator', 'admin'];
        $statuses = ['pending', 'active', 'suspended', 'banned'];
        $privacy = ['public', 'followers', 'private'];

        for ($i = 1; $i <= 120; $i++) {
            $user = new Users();
            $user->setUsername(strtolower($this->faker->unique()->userName()) . $i);
            $user->setName($this->faker->name());
            $user->setEmail($this->faker->unique()->safeEmail());

            $hash = $this->passwordEncoder->encodePassword($user, 'Tagflow123!');

            if (method_exists($user, 'setPasswordHash')) {
                $user->setPasswordHash($hash);
            } elseif (method_exists($user, 'setPassword')) {
                $user->setPassword($hash);
            }

            if (method_exists($user, 'setProfileImageUrl')) {
                $user->setProfileImageUrl($this->chance(85) ? $this->faker->imageUrl(400, 400, 'people', true) : null);
            }

            if (method_exists($user, 'setBio')) {
                $user->setBio($this->chance(80) ? $this->faker->realTextBetween(80, 260) : null);
            }

            if (method_exists($user, 'setWebsiteUrl')) {
                $user->setWebsiteUrl($this->chance(35) ? $this->faker->url() : null);
            }

            if (method_exists($user, 'setBirthDate')) {
                $user->setBirthDate($this->chance(55) ? $this->randomDateTime('-45 years', '-18 years') : null);
            }

            if (method_exists($user, 'setRole')) {
                $user->setRole($i <= 2 ? 'admin' : ($i <= 7 ? 'moderator' : $this->pick($roles)));
            }

            if (method_exists($user, 'setAccountStatus')) {
                $user->setAccountStatus($i <= 110 ? 'active' : $this->pick($statuses));
            }

            if (method_exists($user, 'setPrivacyLevel')) {
                $user->setPrivacyLevel($this->pick($privacy));
            }

            if (method_exists($user, 'setIsVerified')) {
                $user->setIsVerified($this->chance(70));
            }

            if (method_exists($user, 'setIsActive')) {
                $user->setIsActive(!$this->chance(4));
            }

            if (method_exists($user, 'setEmailVerifiedAt')) {
                $user->setEmailVerifiedAt($this->chance(70) ? $this->randomDateTime('-1 year', 'now') : null);
            }

            if (method_exists($user, 'setLastLoginAt')) {
                $user->setLastLoginAt($this->chance(80) ? $this->randomDateTime('-45 days', 'now') : null);
            }

            if (method_exists($user, 'setCreatedAt')) {
                $user->setCreatedAt($this->randomDateTime('-2 years', '-2 months'));
            }

            if (method_exists($user, 'setUpdatedAt')) {
                $user->setUpdatedAt($this->randomDateTime('-2 months', 'now'));
            }

            if (method_exists($user, 'setDeletedAt')) {
                $user->setDeletedAt(null);
            }

            $manager->persist($user);
            $this->addReference(FixtureRefs::USER . $i, $user);
        }

        $demo = new Users();
        $demo->setUsername('demo');
        $demo->setName('Usuario Demo');
        $demo->setEmail('demo@tagflow.local');

        $hash = $this->passwordEncoder->encodePassword($demo, 'Tagflow123!');

        if (method_exists($demo, 'setPasswordHash')) {
            $demo->setPasswordHash($hash);
        } elseif (method_exists($demo, 'setPassword')) {
            $demo->setPassword($hash);
        }

        if (method_exists($demo, 'setProfileImageUrl')) {
            $demo->setProfileImageUrl('https://picsum.photos/400/400');
        }

        if (method_exists($demo, 'setBio')) {
            $demo->setBio('Cuenta demo para pruebas completas de la API.');
        }

        if (method_exists($demo, 'setWebsiteUrl')) {
            $demo->setWebsiteUrl('https://tagflow.local');
        }

        if (method_exists($demo, 'setBirthDate')) {
            $demo->setBirthDate(new \DateTime('1998-06-15'));
        }

        if (method_exists($demo, 'setRole')) {
            $demo->setRole('admin');
        }

        if (method_exists($demo, 'setAccountStatus')) {
            $demo->setAccountStatus('active');
        }

        if (method_exists($demo, 'setPrivacyLevel')) {
            $demo->setPrivacyLevel('public');
        }

        if (method_exists($demo, 'setIsVerified')) {
            $demo->setIsVerified(true);
        }

        if (method_exists($demo, 'setIsActive')) {
            $demo->setIsActive(true);
        }

        if (method_exists($demo, 'setEmailVerifiedAt')) {
            $demo->setEmailVerifiedAt(new \DateTime('-1 year'));
        }

        if (method_exists($demo, 'setLastLoginAt')) {
            $demo->setLastLoginAt(new \DateTime('-1 hour'));
        }

        if (method_exists($demo, 'setCreatedAt')) {
            $demo->setCreatedAt(new \DateTime('-1 year'));
        }

        if (method_exists($demo, 'setUpdatedAt')) {
            $demo->setUpdatedAt(new \DateTime());
        }

        if (method_exists($demo, 'setDeletedAt')) {
            $demo->setDeletedAt(null);
        }

        $manager->persist($demo);
        $this->addReference(FixtureRefs::USER . 'demo', $demo);

        $manager->flush();
    }
}