<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @ORM\Entity
 * @HasLifecycleCallbacks()
 */
class StaffEventLog
{
    // actions
    const
        ACTION_CREATE = 'create',
        ACTION_READ = 'read',
        ACTION_UPDATE = 'update',
        ACTION_DELETE = 'delete',
        ACTION_INVALID_TOKEN_USAGE = 'invalid token usage',
        ACTION_LOGIN_SUCCESSFUL = 'login successful',
        ACTION_LOGIN_UNSUCCESSFUL = 'login unsuccessful',
        ACTION_PASSWORD_CHANGED = 'password changed',
        ACTION_INVALID_RESET_EMAIL = 'invalid reset email';

    /**
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Staff")
     * @ORM\JoinColumn(name="staff_id", referencedColumnName="id", nullable=true)
     * @var Staff
     */
    private $staff;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(64)
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $resource;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(32)
     * @ORM\Column(type="string", length=32)
     * @var string
     */
    private $action;

    /**
     * @ORM\Column(type="json", options={"default"="{}"})
     * @var array
     */
    private $data = [];

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @param array $requestData
     * @param Staff|null $staff
     * @return StaffEventLog
     */
    public static function createInvalidTokenUsageLog(array $requestData, Staff $staff=null)
    {
        $log = new StaffEventLog();
        if ($staff) {
            $log->setStaff($staff);
        }
        $log->setAction(StaffEventLog::ACTION_INVALID_TOKEN_USAGE);
        $log->setResource(Staff::class);
        $log->setData($requestData);
        return $log;
    }

    /**
     * @param array $requestData
     * @param string $email
     * @return StaffEventLog
     */
    public static function createInvalidResetEmailLog(array $requestData, string $email)
    {
        $log = new StaffEventLog();
        $log->setAction(StaffEventLog::ACTION_INVALID_TOKEN_USAGE);
        $log->setResource(Staff::class);
        $log->setData(array_merge($requestData, ['email' => $email]));
        return $log;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Staff|null
     */
    public function getStaff(): Staff
    {
        return $this->staff;
    }

    /**
     * @param Staff $staff
     * @return StaffEventLog
     */
    public function setStaff(Staff $staff): StaffEventLog
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     * @return StaffEventLog
     */
    public function setResource(string $resource): StaffEventLog
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return StaffEventLog
     */
    public function setAction(string $action): StaffEventLog
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return StaffEventLog
     */
    public function setData(array $data): StaffEventLog
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    // pre/post persist ------------------------------------------------------------------------------------------------

    /**
     * @PrePersist()
     */
    public function doCreatedAtPrePersist(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }
}
