<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * SavedPosts
 *
 * @ORM\Table(name="saved_posts", uniqueConstraints={@ORM\UniqueConstraint(name="ux_saved_posts", columns={"user_id", "post_id"})}, indexes={@ORM\Index(name="ix_saved_posts_user_saved_at", columns={"user_id", "saved_at"}), @ORM\Index(name="ix_saved_posts_post", columns={"post_id"}), @ORM\Index(name="IDX_E58E61E3A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class SavedPosts
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
     * @ORM\Column(name="saved_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"internal"})
     */
    private $savedAt = 'CURRENT_TIMESTAMP';

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

    /**
     * @return \DateTime|string
     */
    public function getSavedAt()
    {
        return $this->savedAt;
    }

    /**
     * @param \DateTime|string $savedAt
     */
    public function setSavedAt($savedAt): void
    {
        $this->savedAt = $savedAt;
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
