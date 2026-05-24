<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * Conversations
 *
 * @ORM\Table(name="conversations", indexes={@ORM\Index(name="fk_conversations_created_by", columns={"created_by"}), @ORM\Index(name="ix_conversations_type_created", columns={"conversation_type", "created_at"})})
 * @ORM\Entity
 */
class Conversations
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"conversation:list,conversation:detail,notification:list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="conversation_type", type="string", length=0, nullable=false, options={"default"="direct"})
     * @GROUPS({"conversation:list,conversation:detail"})
     */
    private $conversationType = 'direct';

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=true)
     * @GROUPS({"conversation:list,conversation:detail"})
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"conversation:list,conversation:detail"})
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ConversationParticipants",
     *     mappedBy="conversation"
     * )
     */
    private $participants;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"conversation:list,conversation:detail"})
     */
    private $updatedAt;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     * @GROUPS({"conversation:list,internal"})
     */
    private $createdBy;

    public function __construct()
    {
        $this->participants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getConversationType(): string
    {
        return $this->conversationType;
    }

    public function setConversationType(string $conversationType): void
    {
        $this->conversationType = $conversationType;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
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

    public function getCreatedBy(): Users
    {
        return $this->createdBy;
    }

    public function setCreatedBy(Users $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getParticipants()
    {
        return $this->participants;
    }

    public function addParticipant(ConversationParticipants $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setConversation($this);
        }

        return $this;
    }

    public function removeParticipant(ConversationParticipants $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }

}
