<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * Follows
 *
 * @ORM\Table(name="follows", uniqueConstraints={@ORM\UniqueConstraint(name="ux_follow", columns={"follower_id", "followed_id"})}, indexes={@ORM\Index(name="ix_follow_follower", columns={"follower_id", "created_at"}), @ORM\Index(name="ix_follow_followed", columns={"followed_id", "created_at"}), @ORM\Index(name="IDX_4B638A73D956F010", columns={"followed_id"}), @ORM\Index(name="IDX_4B638A73AC24F853", columns={"follower_id"})})
 * @ORM\Entity
 */
class Follows
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
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"user:detail,internal"})
     * 
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="followed_id", referencedColumnName="id")
     * })
     * @GROUPS({"internal,report:read"})
     */
    private $followed;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="follower_id", referencedColumnName="id")
     * })
     * @GROUPS({"internal,report:read"})
     */
    private $follower;

    public function getId(): int
    {
        return $this->id;
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

    public function getFollowed(): Users
    {
        return $this->followed;
    }

    public function setFollowed(Users $followed): void
    {
        $this->followed = $followed;
    }

    public function getFollower(): Users
    {
        return $this->follower;
    }

    public function setFollower(Users $follower): void
    {
        $this->follower = $follower;
    }

}
