<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * Messages
 *
 * @ORM\Table(name="messages", indexes={@ORM\Index(name="ix_messages_conversation_created", columns={"conversation_id", "created_at"}), @ORM\Index(name="ix_messages_sender_created", columns={"sender_id", "created_at"}), @ORM\Index(name="ix_messages_deleted_at", columns={"deleted_at"}), @ORM\Index(name="IDX_DB021E969AC0396", columns={"conversation_id"}), @ORM\Index(name="IDX_DB021E96F624B39D", columns={"sender_id"})})
 * @ORM\Entity
 */
class Messages
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"message:list,conversation:detail,notification:list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     * @GROUPS({"message:list,message:write"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="message_type", type="string", length=0, nullable=false, options={"default"="text"})
     * @GROUPS({"message:list,message:write"})
     */
    private $messageType = 'text';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_edited", type="boolean", nullable=false)
     * @GROUPS({"message:list"})
     */
    private $isEdited = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"message:list"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"message:list"})
     */
    private $updatedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @GROUPS({"internal"})
     */
    private $deletedAt;

    /**
     * @var Conversations
     *
     * @ORM\ManyToOne(targetEntity="Conversations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="conversation_id", referencedColumnName="id")
     * })
     * @GROUPS({"message:list,internal"})
     */
    private $conversation;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     * })
     * @GROUPS({"message:list,conversation:detail"})
     */
    private $sender;

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): void
    {
        $this->messageType = $messageType;
    }

    /**
     * @return bool|string
     */
    public function getIsEdited()
    {
        return $this->isEdited;
    }

    /**
     * @param bool|string $isEdited
     */
    public function setIsEdited($isEdited): void
    {
        $this->isEdited = $isEdited;
    }

    /**
     * @return \DateTime|string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|string $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime|string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|string $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getConversation(): Conversations
    {
        return $this->conversation;
    }

    public function setConversation(Conversations $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getSender(): Users
    {
        return $this->sender;
    }

    public function setSender(Users $sender): void
    {
        $this->sender = $sender;
    }


}
