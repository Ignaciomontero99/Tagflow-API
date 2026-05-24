<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRefreshTokens
 *
 * @ORM\Table(name="user_refresh_tokens", uniqueConstraints={@ORM\UniqueConstraint(name="ux_user_refresh_tokens_hash", columns={"token_hash"})}, indexes={@ORM\Index(name="ix_user_refresh_tokens_user_expires", columns={"user_id", "expires_at"}), @ORM\Index(name="ix_user_refresh_tokens_revoked_at", columns={"revoked_at"}), @ORM\Index(name="IDX_F02938B8A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class UserRefreshTokens
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
     * @ORM\Column(name="token_hash", type="string", length=64, nullable=false, options={"fixed"=true})
     * @GROUPS({"internal"})
     */
    private $tokenHash;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=false)
     * @GROUPS({"internal"})
     */
    private $expiresAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="revoked_at", type="datetime", nullable=true)
     * @GROUPS({"internal"})
     */
    private $revokedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"internal"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_used_at", type="datetime", nullable=true)
     * @GROUPS({"internal"})
     */
    private $lastUsedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ip_address", type="string", length=45, nullable=true)
     * @GROUPS({"internal"})
     */
    private $ipAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="user_agent", type="string", length=255, nullable=true)
     * @GROUPS({"internal"})
     */
    private $userAgent;

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

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): void
    {
        $this->tokenHash = $tokenHash;
    }

    public function getExpiresAt(): \DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getRevokedAt(): ?\DateTime
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?\DateTime $revokedAt): void
    {
        $this->revokedAt = $revokedAt;
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

    public function getLastUsedAt(): ?\DateTime
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?\DateTime $lastUsedAt): void
    {
        $this->lastUsedAt = $lastUsedAt;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
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
