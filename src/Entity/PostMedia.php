<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostMedia
 *
 * @ORM\Table(name="post_media", indexes={@ORM\Index(name="ix_post_media_post_sort", columns={"post_id", "sort_order"}), @ORM\Index(name="IDX_FD372DE34B89032C", columns={"post_id"})})
 * @ORM\Entity
 */
class PostMedia
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"post:list,post:detail"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="media_url", type="string", length=512, nullable=false)
     * @GROUPS({"post:list,post:detail"})
     */
    private $mediaUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="media_type", type="string", length=0, nullable=false, options={"default"="image"})
     * @GROUPS({"post:list,post:detail"})
     */
    private $mediaType = 'image';

    /**
     * @var string|null
     *
     * @ORM\Column(name="mime_type", type="string", length=100, nullable=true)
     * @GROUPS({"post:list,internal"})
     */
    private $mimeType;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="smallint", nullable=false, options={"unsigned"=true})
     * @GROUPS({"post:list,post:detail"})
     */
    private $sortOrder = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="width", type="integer", nullable=true, options={"unsigned"=true})
     * @GROUPS({"post:detail"})
     */
    private $width;

    /**
     * @var int|null
     *
     * @ORM\Column(name="height", type="integer", nullable=true, options={"unsigned"=true})
     * @GROUPS({"post:detail"})
     */
    private $height;

    /**
     * @var int|null
     *
     * @ORM\Column(name="duration_seconds", type="integer", nullable=true, options={"unsigned"=true})
     * @GROUPS({"post:detail"})
     */
    private $durationSeconds;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getMediaUrl(): string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): void
    {
        $this->mediaUrl = $mediaUrl;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return int|string
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int|string $sortOrder
     */
    public function setSortOrder($sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getDurationSeconds(): ?int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(?int $durationSeconds): void
    {
        $this->durationSeconds = $durationSeconds;
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
}
