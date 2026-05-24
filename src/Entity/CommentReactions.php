<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommentReactions
 *
 * @ORM\Table(name="comment_reactions", uniqueConstraints={@ORM\UniqueConstraint(name="ux_comment_reaction", columns={"user_id", "comment_id"})}, indexes={@ORM\Index(name="ix_comment_reactions_comment_type", columns={"comment_id", "type"}), @ORM\Index(name="ix_comment_reactions_user_created", columns={"user_id", "created_at"}), @ORM\Index(name="IDX_D10D9EE5F8697D13", columns={"comment_id"}), @ORM\Index(name="IDX_D10D9EE5A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class CommentReactions
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
     * @GROUPS({"comment:detail,internal"})
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
     * @var Comments
     *
     * @ORM\ManyToOne(targetEntity="Comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="comment_id", referencedColumnName="id")
     * })
     * @GROUPS({"internal"})
     */
    private $comment;

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

    public function getComment(): Comments
    {
        return $this->comment;
    }

    public function setComment(Comments $comment): void
    {
        $this->comment = $comment;
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
