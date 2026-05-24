<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageReads
 *
 * @ORM\Table(name="message_reads", uniqueConstraints={@ORM\UniqueConstraint(name="ux_message_read", columns={"message_id", "user_id"})}, indexes={@ORM\Index(name="ix_message_reads_user_read_at", columns={"user_id", "read_at"}), @ORM\Index(name="IDX_37E6935A537A1329", columns={"message_id"}), @ORM\Index(name="IDX_37E6935AA76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class MessageReads
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"internal"})
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="read_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"internal"})
     */
    private $readAt = 'CURRENT_TIMESTAMP';

    /**
     * @var Messages
     *
     * @ORM\ManyToOne(targetEntity="Messages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="message_id", referencedColumnName="id")
     * })
     * @GROUPS({"internal"})
     */
    private $message;

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

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime|string
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * @param \DateTime|string $readAt
     */
    public function setReadAt($readAt): void
    {
        $this->readAt = $readAt;
    }

    public function getMessage(): Messages
    {
        return $this->message;
    }

    public function setMessage(Messages $message): void
    {
        $this->message = $message;
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
