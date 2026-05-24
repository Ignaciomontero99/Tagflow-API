<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * LoginAttempts
 *
 * @ORM\Table(name="login_attempts", indexes={@ORM\Index(name="ix_login_attempts_email_attempted", columns={"email", "attempted_at"}), @ORM\Index(name="ix_login_attempts_ip_attempted", columns={"ip_address", "attempted_at"})})
 * @ORM\Entity
 */
class LoginAttempts
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
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=150, nullable=false)
     * @GROUPS({"internal"})
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ip_address", type="string", length=45, nullable=true)
     * @GROUPS({"internal"})
     */
    private $ipAddress;

    /**
     * @var bool
     *
     * @ORM\Column(name="success", type="boolean", nullable=false)
     * @GROUPS({"internal"})
     */
    private $success = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="attempted_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"internal"})
     */
    private $attemptedAt = 'CURRENT_TIMESTAMP';

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return bool|string
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool|string $success
     */
    public function setSuccess($success): void
    {
        $this->success = $success;
    }

    /**
     * @return \DateTime|string
     */
    public function getAttemptedAt()
    {
        return $this->attemptedAt;
    }

    /**
     * @param \DateTime|string $attemptedAt
     */
    public function setAttemptedAt($attemptedAt): void
    {
        $this->attemptedAt = $attemptedAt;
    }
}
