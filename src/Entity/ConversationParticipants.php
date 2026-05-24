<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConversationParticipants
 *
 * @ORM\Table(name="conversation_participants", uniqueConstraints={@ORM\UniqueConstraint(name="ux_conversation_participant", columns={"conversation_id", "user_id"})}, indexes={@ORM\Index(name="ix_conversation_participants_user", columns={"user_id"}), @ORM\Index(name="ix_conversation_participants_last_read", columns={"last_read_message_id"}), @ORM\Index(name="IDX_21821ED39AC0396", columns={"conversation_id"})})
 * @ORM\Entity
 */
class ConversationParticipants
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"conversation:detail, internal"})
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="joined_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"conversation:detail"})
     */
    private $joinedAt ;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="left_at", type="datetime", nullable=true)
     * @GROUPS({"conversation:detail, internal"})
     */
    private $leftAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_admin", type="boolean", nullable=false)
     * @GROUPS({"conversation:detail"})
     */
    private $isAdmin = '0';

    /**
     * @var Conversations
     *
     * @ORM\ManyToOne(targetEntity="Conversations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="conversation_id", referencedColumnName="id")
     * })
     * @GROUPS({"internal"})
     */
    private $conversation;

    /**
     * @var Messages
     *
     * @ORM\ManyToOne(targetEntity="Messages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="last_read_message_id", referencedColumnName="id", nullable=true)
     * })
     * @GROUPS({"conversation:detail, internal"})
     */
    private $lastReadMessage;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     * @GROUPS({"conversation:detail, conversation:list"})
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime|string
     */
    public function getJoinedAt()
    {
        return $this->joinedAt;
    }

    /**
     * @param \DateTime|string $joinedAt
     */
    public function setJoinedAt($joinedAt): void
    {
        $this->joinedAt = $joinedAt;
    }

    public function getLeftAt(): ?\DateTime
    {
        return $this->leftAt;
    }

    public function setLeftAt(?\DateTime $leftAt): void
    {
        $this->leftAt = $leftAt;
    }

    /**
     * @return bool|string
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @param bool|string $isAdmin
     */
    public function setIsAdmin($isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }

    public function getConversation(): Conversations
    {
        return $this->conversation;
    }

    public function setConversation(Conversations $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getLastReadMessage(): ?Messages
    {
        return $this->lastReadMessage;
    }

    public function setLastReadMessage(?Messages $lastReadMessage): self
    {
        $this->lastReadMessage = $lastReadMessage;

        return $this;
    }

    public function getUser(): Users
    {
        return $this->user;
    }

    public function setUser(Users $user): void
    {
        $this->user = $user;
    }

}
