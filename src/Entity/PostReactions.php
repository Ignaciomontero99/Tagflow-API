<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostReactions
 *
 * @ORM\Table(name="post_reactions", uniqueConstraints={@ORM\UniqueConstraint(name="ux_post_reaction", columns={"user_id", "post_id"})}, indexes={@ORM\Index(name="ix_post_reactions_post_type", columns={"post_id", "type"}), @ORM\Index(name="ix_post_reactions_user_created", columns={"user_id", "created_at"}), @ORM\Index(name="IDX_991D01444B89032C", columns={"post_id"}), @ORM\Index(name="IDX_991D0144A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class PostReactions
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
     * @ORM\Column(name="type", type="string", length=0, nullable=false, options={"default"="like"})
     * @GROUPS({"post:detail, internal"})
     */
    private $type = 'like';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"internal"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var Posts
     *
     * @ORM\ManyToOne(targetEntity="Posts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     * })
     * @GROUPS({"internal"})
     */
    private $post;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
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

    public function getPost(): Posts
    {
        return $this->post;
    }

    public function setPost(Posts $post): void
    {
        $this->post = $post;
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
