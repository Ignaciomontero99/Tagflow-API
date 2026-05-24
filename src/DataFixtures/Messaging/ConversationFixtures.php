<?php

namespace App\DataFixtures\Messaging;

use App\DataFixtures\Base\BaseFixture;
use App\DataFixtures\Base\UserFixtures;
use App\DataFixtures\FixtureRefs;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\ConversationParticipants;
use App\Entity\Conversations;
use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConversationFixtures extends BaseFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['conversations', 'messaging', 'all'];
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 220; $i++) {
            $creator = $this->getReference(FixtureRefs::USER . $this->faker->numberBetween(1, 120), Users::class);

            $conversation = new Conversations();
            $conversation->setConversationType($this->pick(['direct', 'group', 'direct', 'direct']));
            $conversation->setTitle($conversation->getConversationType() === 'group' ? 'Grupo ' . ucfirst($this->faker->words(2, true)) : null);
            $conversation->setCreatedBy($creator);
            $conversation->setCreatedAt($this->randomDateTime('-8 months', 'now'));
            $conversation->setUpdatedAt($this->randomDateTime('-2 months', 'now'));

            $manager->persist($conversation);
            $this->addReference(FixtureRefs::CONVERSATION . $i, $conversation);

            $participantIds = $conversation->getConversationType() === 'group'
                ? $this->faker->randomElements(range(1, 120), $this->faker->numberBetween(3, 8))
                : $this->faker->randomElements(range(1, 120), 2);

            foreach ($participantIds as $userId) {
                $user = $this->getReference(FixtureRefs::USER . $userId, Users::class);

                $participant = new ConversationParticipants();
                $participant->setConversation($conversation);
                $participant->setUser($user);
                $participant->setJoinedAt($this->randomDateTime('-8 months', 'now'));
                $participant->setLeftAt(null);
                $participant->setIsAdmin($conversation->getConversationType() === 'group' && $user === $creator);
                if (method_exists($participant, 'setLastReadMessage')) {
                    $participant->setLastReadMessage(null);
                } else {
                    $participant->setLastReadMessageId(null);
                }

                $manager->persist($participant);
            }
        }

        $manager->flush();
    }
}
