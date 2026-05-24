<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tags
 *
 * @ORM\Table(name="tags", uniqueConstraints={@ORM\UniqueConstraint(name="ux_tags_name", columns={"name"}), @ORM\UniqueConstraint(name="ux_tags_slug", columns={"slug"})}, indexes={@ORM\Index(name="ix_tags_parent", columns={"parent_id"}), @ORM\Index(name="ix_tags_usage_count", columns={"usage_count"})})
 * @ORM\Entity
 */
class Tags
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"tag:read,post:detail,user:detail"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     * @GROUPS({"tag:read"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=120, nullable=false)
     * @GROUPS({"tag:read"})
     */
    private $slug;

    /**
     * @var int
     *
     * @ORM\Column(name="usage_count", type="integer", nullable=false, options={"unsigned"=true})
     * @GROUPS({"tag:read,internal"})
     */
    private $usageCount = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"internal"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"internal"})
     */
    private $updatedAt;

    /**
     * @var Tags
     *
     * @ORM\ManyToOne(targetEntity="Tags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * })
     * @GROUPS({"tag:read,internal"})
     * @MaxDepth(1)
     */
    private $parent;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Posts", mappedBy="tag")
     */
    private $post;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->post = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return int|string
     */
    public function getUsageCount()
    {
        return $this->usageCount;
    }

    /**
     * @param int|string $usageCount
     */
    public function setUsageCount($usageCount): void
    {
        $this->usageCount = $usageCount;
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

    public function getParent(): ?Tags
    {
        return $this->parent;
    }

    public function setParent(?Tags $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param ArrayCollection|Collection $post
     */
    public function setPost($post): void
    {
        $this->post = $post;
    }

}