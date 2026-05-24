<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comments
 *
 * @ORM\Table(name="comments", indexes={@ORM\Index(name="ix_comments_post_created", columns={"post_id", "created_at"}), @ORM\Index(name="ix_comments_parent_created", columns={"parent_comment_id", "created_at"}), @ORM\Index(name="ix_comments_user_created", columns={"user_id", "created_at"}), @ORM\Index(name="ix_comments_deleted_at", columns={"deleted_at"}), @ORM\Index(name="IDX_5F9E962ABF2AF943", columns={"parent_comment_id"}), @ORM\Index(name="IDX_5F9E962A4B89032C", columns={"post_id"}), @ORM\Index(name="IDX_5F9E962AA76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class Comments
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"comment:list, comment:detail, notification:list, report:read"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     * @GROUPS({"comment:list, comment:detail, comment:write"})
     */
    private $content;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_edited", type="boolean", nullable=false)
     * @GROUPS({"comment:list, comment:detail"})
     */
    private $isEdited = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="reaction_count", type="integer", nullable=false, options={"unsigned"=true})
     * @GROUPS({"comment:list, comment:detail"})
     */
    private $reactionCount = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="reply_count", type="integer", nullable=false, options={"unsigned"=true})
     * @GROUPS({"comment:list, comment:detail"})
     */
    private $replyCount = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"comment:list, comment:detail"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"comment:detail"})
     */
    private $updatedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @GROUPS({"internal"})
     */
    private $deletedAt;

    /**
     * @var Comments
     *
     * @ORM\ManyToOne(targetEntity="Comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_comment_id", referencedColumnName="id", nullable=true)
     * })
     * @GROUPS({"comment:list, comment:detail"})
     */
    private $parentComment;

    /**
     * @var Posts
     *
     * @ORM\ManyToOne(targetEntity="Posts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     * })
     * @GROUPS({"comment:list, comment:detail, internal"})
     */
    private $post;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     * @GROUPS({"comment:list, comment:detail, internal"})
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return bool|string
     */
    public function getIsEdited()
    {
        return $this->isEdited;
    }

    /**
     * @param bool|string $isEdited
     */
    public function setIsEdited($isEdited): void
    {
        $this->isEdited = $isEdited;
    }

    /**
     * @return int|string
     */
    public function getReactionCount()
    {
        return $this->reactionCount;
    }

    /**
     * @param int|string $reactionCount
     */
    public function setReactionCount($reactionCount): void
    {
        $this->reactionCount = $reactionCount;
    }

    /**
     * @return int|string
     */
    public function getReplyCount()
    {
        return $this->replyCount;
    }

    /**
     * @param int|string $replyCount
     */
    public function setReplyCount($replyCount): void
    {
        $this->replyCount = $replyCount;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
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

    public function getParentComment(): ?Comments
    {
        return $this->parentComment;
    }

    public function setParentComment(?Comments $parentComment): self
    {
        $this->parentComment = $parentComment;

        return $this;
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
