<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserReports
 *
 * @ORM\Table(name="user_reports", indexes={@ORM\Index(name="fk_user_reports_reporter", columns={"reporter_id"}), @ORM\Index(name="ix_user_reports_status_created", columns={"status", "created_at"}), @ORM\Index(name="ix_user_reports_reported_user", columns={"reported_user_id"})})
 * @ORM\Entity
 */
class UserReports
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @GROUPS({"report:read"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=255, nullable=false)
     * @GROUPS({"report:read,report:write"})
     */
    private $reason;

    /**
     * @var string|null
     *
     * @ORM\Column(name="details", type="text", length=65535, nullable=true)
     * @GROUPS({"report:read,report:write"})
     */
    private $details;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=0, nullable=false, options={"default"="open"})
     * @GROUPS({"report:read"})
     */
    private $status = 'open';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     * @GROUPS({"report:read"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="resolved_at", type="datetime", nullable=true)
     * @GROUPS({"report:read"})
     */
    private $resolvedAt;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reported_user_id", referencedColumnName="id")
     * })
     * @GROUPS({"report:read,report:write"})
     */
    private $reportedUser;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reporter_id", referencedColumnName="id")
     * })
     * @GROUPS({"report:read,internal"})
     */
    private $reporter;

    public function getId(): int
    {
        return $this->id;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): void
    {
        $this->details = $details;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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

    public function getResolvedAt(): ?\DateTime
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTime $resolvedAt): void
    {
        $this->resolvedAt = $resolvedAt;
    }

    public function getReportedUser(): Users
    {
        return $this->reportedUser;
    }

    public function setReportedUser(Users $reportedUser): void
    {
        $this->reportedUser = $reportedUser;
    }

    public function getReporter(): Users
    {
        return $this->reporter;
    }

    public function setReporter(Users $reporter): void
    {
        $this->reporter = $reporter;
    }


}
