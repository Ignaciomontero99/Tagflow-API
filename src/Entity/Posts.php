<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * Posts
 *
 * @ORM\Table(name="posts", indexes={@ORM\Index(name="ix_posts_user_created", columns={"user_id", "created_at"}), @ORM\Index(name="ix_posts_visibility_status_created", columns={"visibility", "status", "created_at"}), @ORM\Index(name="ix_posts_created", columns={"created_at"}), @ORM\Index(name="ix_posts_deleted_at", columns={"deleted_at"}), @ORM\Index(name="IDX_885DBAFAA76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class Posts
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"post:list,post:detail,notification:list,report:read"})
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=true)
     * @GROUPS({"post:list,post:detail,post:write"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="visibility", type="string", length=0, nullable=false, options={"default"="public"})
     * @GROUPS({"post:detail,post:write,internal"})
     */
    private $visibility = 'public';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=0, nullable=false, options={"default"="published"})
     * @GROUPS({"post:detail,post:write,internal"})
     */
    private $status = 'published';

    /**
     * @var bool
     *
     * @ORM\Column(name="comments_enabled", type="boolean", nullable=false, options={"default"="1"})
     * @GROUPS({"post:detail,post:write"})
     */
    private $commentsEnabled = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_ad", type="boolean", nullable=false)
     * @GROUPS({"post:detail,post:write,internal"})
     */
    private $isAd = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="location_name", type="string", length=150, nullable=true)
     * @GROUPS({"post:detail,post:write"})
     */
    private $locationName;

    /**
     * @var int
     *
     * @ORM\Column(name="reaction_count", type="integer", nullable=false, options={"unsigned"=true})
     * @GROUPS({"post:list,post:detail"})
     */
    private $reactionCount = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="comment_count", type="integer", nullable=false, options={"unsigned"=true})
     * @GROUPS({"post:list,post:detail"})
     */
    private $commentCount = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="save_count", type="integer", nullable=false, options={"unsigned"=true})
     * @GROUPS({"post:detail,internal"})
     */
    private $saveCount = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"post:list,post:detail"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"post:detail"})
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
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     * @GROUPS({"post:list,post:detail,internal"})
     */
    private $user;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Tags", inversedBy="post")
     * @ORM\JoinTable(name="post_tag",
     *   joinColumns={
     *     @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     *   }
     * )
     */
    private $tag;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tag = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isCommentsEnabled(): bool
    {
        return $this->commentsEnabled;
    }

    public function setCommentsEnabled(bool $commentsEnabled): void
    {
        $this->commentsEnabled = $commentsEnabled;
    }

    /**
     * @return bool|string
     */
    public function getIsAd()
    {
        return $this->isAd;
    }

    /**
     * @param bool|string $isAd
     */
    public function setIsAd($isAd): void
    {
        $this->isAd = $isAd;
    }

    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    public function setLocationName(?string $locationName): void
    {
        $this->locationName = $locationName;
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
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    /**
     * @param int|string $commentCount
     */
    public function setCommentCount($commentCount): void
    {
        $this->commentCount = $commentCount;
    }

    /**
     * @return int|string
     */
    public function getSaveCount()
    {
        return $this->saveCount;
    }

    /**
     * @param int|string $saveCount
     */
    public function setSaveCount($saveCount): void
    {
        $this->saveCount = $saveCount;
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

    public function getUser(): Users
    {
        return $this->user;
    }

    public function setUser(Users $user): void
    {
        $this->user = $user;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param ArrayCollection|Collection $tag
     */
    public function setTag($tag): void
    {
        $this->tag = $tag;
    }

    public function addTag(Tags $tag): self
    {
        if (!$this->tag->contains($tag)) {
            $this->tag[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tags $tag): self
    {
        $this->tag->removeElement($tag);

        return $this;
    }

}
