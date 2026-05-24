<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notifications
 *
 * @ORM\Table(name="notifications", indexes={@ORM\Index(name="fk_notifications_sender", columns={"sender_id"}), @ORM\Index(name="ix_notifications_user_read_created", columns={"user_id", "is_read", "created_at"}), @ORM\Index(name="ix_notifications_reference", columns={"reference_type", "reference_id"}), @ORM\Index(name="IDX_6000B0D3A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class Notifications
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"notification:list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=0, nullable=false)
     * @GROUPS({"notification:list"})
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=true)
     * @GROUPS({"notification:list"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message", type="string", length=500, nullable=true)
     * @GROUPS({"notification:list"})
     */
    private $message;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reference_type", type="string", length=0, nullable=true)
     * @GROUPS({"notification:list"})
     */
    private $referenceType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="reference_id", type="bigint", nullable=true, options={"unsigned"=true})
     * @GROUPS({"notification:list"})
     */
    private $referenceId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_read", type="boolean", nullable=false)
     * @GROUPS({"notification:list"})
     */
    private $isRead = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="read_at", type="datetime", nullable=true)
     * @GROUPS({"notification:list"})
     */
    private $readAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"notification:list"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     * @GROUPS({"internal"})
     */
    private $user;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=true)
     * })
     * @GROUPS({"notification:list,internal"})
     */
    private $sender;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getReferenceType(): ?string
    {
        return $this->referenceType;
    }

    public function setReferenceType(?string $referenceType): void
    {
        $this->referenceType = $referenceType;
    }

    public function getReferenceId(): ?int
    {
        return $this->referenceId;
    }

    public function setReferenceId(?int $referenceId): void
    {
        $this->referenceId = $referenceId;
    }

    /**
     * @return bool|string
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * @param bool|string $isRead
     */
    public function setIsRead($isRead): void
    {
        $this->isRead = $isRead;
    }

    public function getReadAt(): ?\DateTime
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTime $readAt): void
    {
        $this->readAt = $readAt;
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

    public function getUser(): Users
    {
        return $this->user;
    }

    public function setUser(Users $user): void
    {
        $this->user = $user;
    }

    public function getSender(): ?Users
    {
        return $this->sender;
    }

    public function setSender(?Users $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

}
