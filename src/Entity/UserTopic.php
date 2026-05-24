<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserTopic
 *
 * @ORM\Table(name="user_topic", uniqueConstraints={@ORM\UniqueConstraint(name="ux_user_topic", columns={"user_id", "tag_id"})}, indexes={@ORM\Index(name="ix_user_topic_tag", columns={"tag_id"}), @ORM\Index(name="IDX_7F822543A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class UserTopic
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
     * @GROUPS({"internal"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var Tags
     *
     * @ORM\ManyToOne(targetEntity="Tags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     * @GROUPS({"user:detail,internal"})
     */
    private $tag;

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

    public function getTag(): Tags
    {
        return $this->tag;
    }

    public function setTag(Tags $tag): void
    {
        $this->tag = $tag;
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
