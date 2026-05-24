<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="ux_users_username", columns={"username"}), @ORM\UniqueConstraint(name="ux_users_email", columns={"email"})}, indexes={@ORM\Index(name="ix_users_username", columns={"username"}), @ORM\Index(name="ix_users_email", columns={"email"}), @ORM\Index(name="ix_users_role_status", columns={"role", "account_status"}), @ORM\Index(name="ix_users_created_at", columns={"created_at"}), @ORM\Index(name="ix_users_deleted_at", columns={"deleted_at"})})
 * @ORM\Entity
 */
class Users implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"auth:read,user:list,user:detail,conversation:list,conversation:detail,notification:list,report:read"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, nullable=false)
     * @GROUPS({"auth:read,user:list,user:detail"})
     */
    private $username;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=120, nullable=true)
     * @GROUPS({"auth:read,user:list,user:detail,post:list,post:detail,comment:list,message:list"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=150, nullable=false)
     * @GROUPS({"auth:read,user:detail,internal"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password_hash", type="string", length=255, nullable=false)
     * @GROUPS({"internal"})
     */
    private $passwordHash;

    /**
     * @var string|null
     *
     * @ORM\Column(name="profile_image_url", type="string", length=512, nullable=true)
     * @GROUPS({"auth:read,user:list,user:detail"})
     */
    private $profileImageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bio", type="string", length=500, nullable=true)
     * @GROUPS({"user:detail,user:write"})
     */
    private $bio;

    /**
     * @var string|null
     *
     * @ORM\Column(name="website_url", type="string", length=255, nullable=true)
     * @GROUPS({"user:detail,user:write"})
     */
    private $websiteUrl;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="birth_date", type="date", nullable=true)
     * @GROUPS({"user:detail,user:write"})
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=0, nullable=false, options={"default"="user"})
     * @GROUPS({"auth:read,user:admin"})
     */
    private $role = 'user';

    /**
     * @var string
     *
     * @ORM\Column(name="account_status", type="string", length=0, nullable=false, options={"default"="active"})
     * @GROUPS({"auth:read,user:admin"})
     */
    private $accountStatus = 'active';

    /**
     * @var string
     *
     * @ORM\Column(name="privacy_level", type="string", length=0, nullable=false, options={"default"="public"})
     * @GROUPS({"user:detail,user:write,user:admin"})
     */
    private $privacyLevel = 'public';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     * @GROUPS({"user:list,user:detail,user:admin"})
     */
    private $isVerified = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false, options={"default"="1"})
     * @GROUPS({"user:admin"})
     */
    private $isActive = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="email_verified_at", type="datetime", nullable=true)
     * @GROUPS({"auth:read,user:admin,internal"})
     */
    private $emailVerifiedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true)
     * @GROUPS({"user:admin,internal"})
     */
    private $lastLoginAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"user:detail,user:admin"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"user:detail,user:admin"})
     */
    private $updatedAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @GROUPS({"internal,user:admin"})
     */
    private $deletedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getProfileImageUrl(): ?string
    {
        return $this->profileImageUrl;
    }

    public function setProfileImageUrl(?string $profileImageUrl): void
    {
        $this->profileImageUrl = $profileImageUrl;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): void
    {
        $this->websiteUrl = $websiteUrl;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getRoles()
    {
        $role = $this->role ?: 'user';

        if (strpos($role, 'ROLE_') !== 0) {
            $role = 'ROLE_' . strtoupper($role);
        }

        return array_unique([$role, 'ROLE_USER']);
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    public function setAccountStatus(string $accountStatus): void
    {
        $this->accountStatus = $accountStatus;
    }

    public function getPrivacyLevel(): string
    {
        return $this->privacyLevel;
    }

    public function setPrivacyLevel(string $privacyLevel): void
    {
        $this->privacyLevel = $privacyLevel;
    }

    /**
     * @return bool|string
     */
    public function getIsVerified()
    {
        return $this->isVerified;
    }

    /**
     * @param bool|string $isVerified
     */
    public function setIsVerified($isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getEmailVerifiedAt(): ?\DateTime
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTime $emailVerifiedAt): void
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
    }

    public function getLastLoginAt(): ?\DateTime
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTime $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
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

    public function getPassword()
    {
        return $this->passwordHash;
    }

    public function setPassword($password): self
    {
        $this->passwordHash = $password;

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsernameUser(): string
    {
        return $this->username;
    }


}
