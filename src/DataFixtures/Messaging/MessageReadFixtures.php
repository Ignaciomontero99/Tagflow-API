<?php

namespace App\DataFixtures\Messaging;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\FixtureRefs;
use App\Entity\Messages;
use App\Entity\MessageReads;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MessageReadFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['message_reads', 'messaging', 'all'];
    }

    public function getDependencies(): array
    {
        return [MessageFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5000; $i++) {
            try {
                $message = $this->getReference(FixtureRefs::MESSAGE . $i, Messages::class);
            } catch (\Throwable $ex) {
                break;
            }

            foreach ($message->getConversation()->getParticipants() as $participant) {
                if ($participant->getUser() === $message->getSender() || !$this->chance(75)) {
                    continue;
                }

                $read = new MessageReads();
                $read->setMessage($message);
                $read->setUser($participant->getUser());
                $read->setReadAt($this->randomDateTime('-6 months', 'now'));

                $manager->persist($read);
            }
        }

        $manager->flush();
    }
}
