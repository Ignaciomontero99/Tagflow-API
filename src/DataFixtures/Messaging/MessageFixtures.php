<?php

namespace App\DataFixtures\Messaging;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\FixtureRefs;
use App\Entity\Conversation;
use App\Entity\Conversations;
use App\Entity\Message;
use App\Entity\Messages;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MessageFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['messages', 'messaging', 'all'];
    }

    public function getDependencies(): array
    {
        return [ConversationFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $index = 1;

        for ($i = 1; $i <= 220; $i++) {
            $conversation = $this->getReference(FixtureRefs::CONVERSATION . $i, Conversations::class);

            $participants = [];
            foreach ($conversation->getParticipants() as $participant) {
                $participants[] = $participant->getUser();
            }

            $total = $this->faker->numberBetween(8, 35);

            for ($j = 0; $j < $total; $j++) {
                $sender = new Users();

                $sender = $this->faker->randomElement($participants);

                $message = new Messages();
                $message->setConversation($conversation);
                $message->setSender($sender);
                $message->setContent($this->faker->realTextBetween(15, 260));
                $message->setMessageType($this->pick(['text', 'text', 'text', 'image', 'video', 'system']));
                $message->setIsEdited($this->chance(6));
                $message->setCreatedAt($this->randomDateTime('-7 months', 'now'));
                $message->setUpdatedAt($this->randomDateTime('-2 months', 'now'));
                $message->setDeletedAt(null);

                $manager->persist($message);
                $this->addReference(FixtureRefs::MESSAGE . $index++, $message);
            }
        }

        $manager->flush();
    }
}
