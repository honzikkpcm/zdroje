<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * Class GuestFile
 * @package App\Entity
 * @ORM\Entity()
 * @HasLifecycleCallbacks()
 */
class GuestFile
{
    // statuses
    const
        STATUS_NEW = 0,
        STATUS_APPROVED = 1,
        STATUS_REJECTED = -1;

    /**
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Guest")
     * @ORM\JoinColumn(name="guest_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Guest
     */
    private $guest;

    /**
     * @ORM\ManyToOne(targetEntity="GuestChallenge")
     * @ORM\JoinColumn(name="guest_challenge_id", referencedColumnName="id", onDelete="CASCADE")
     * @var GuestChallenge
     */
    private $guestChallenge;

    /**
     * @ORM\ManyToOne(targetEntity="ChallengeTask")
     * @ORM\JoinColumn(name="challenge_task_id", referencedColumnName="id", onDelete="CASCADE")
     * @var ChallengeTask
     */
    private $challengeTask;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     * @ORM\Column(type="string", length=256)
     * @var string
     */
    private $url;

    /**
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string
     */
    private $name;

    /**
     * @Assert\LessThanOrEqual(32)
     * @ORM\Column(type="string", length=32, nullable=true)
     * @var string
     */
    private $type;

    /**
     * @Assert\Type(type="int")
     * @Assert\Choice(callback="getStatuses")
     * @ORM\Column(type="smallint", options={"default"="0"})
     * @var int
     */
    private $status = 0;

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

    // internal --------------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_NEW,
            self::STATUS_REJECTED,
        ];
    }

    /**
     * @param int $status
     * @return string
     */
    public static function convertStatusString(int $status): string
    {
        switch ($status) {
            case self::STATUS_NEW: return 'new'; break;
            case self::STATUS_APPROVED: return 'approved'; break;
            case self::STATUS_REJECTED: return 'rejected'; break;
        }
    }

    /**
     * @param string $status
     * @return int
     */
    public static function convertStatusInt(string $status): int
    {
        switch ($status) {
            case 'new': return self::STATUS_NEW; break;
            case 'approved': return self::STATUS_APPROVED; break;
            case 'rejected': return self::STATUS_REJECTED; break;
        }
    }

    // getters/setters -------------------------------------------------------------------------------------------------

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Guest
     */
    public function getGuest(): Guest
    {
        return $this->guest;
    }

    /**
     * @param Guest $guest
     * @return GuestFile
     */
    public function setGuest(Guest $guest): GuestFile
    {
        $this->guest = $guest;
        return $this;
    }

    /**
     * @return GuestChallenge
     */
    public function getGuestChallenge(): GuestChallenge
    {
        return $this->guestChallenge;
    }

    /**
     * @param GuestChallenge $guestChallenge
     * @return GuestFile
     */
    public function setGuestChallenge(GuestChallenge $guestChallenge): GuestFile
    {
        $this->guestChallenge = $guestChallenge;
        return $this;
    }

    /**
     * @return ChallengeTask
     */
    public function getChallengeTask(): ChallengeTask
    {
        return $this->challengeTask;
    }

    /**
     * @param ChallengeTask $challengeTask
     * @return GuestFile
     */
    public function setChallengeTask(ChallengeTask $challengeTask): GuestFile
    {
        $this->challengeTask = $challengeTask;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return GuestFile
     */
    public function setUrl(string $url): GuestFile
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return GuestFile
     */
    public function setName(string $name): GuestFile
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return GuestFile
     */
    public function setType(string $type): GuestFile
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return GuestFile
     */
    public function setStatus(int $status): GuestFile
    {
        $this->status = $status;
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
     * @return GuestFile
     */
    public function setData(array $data): GuestFile
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
     * @return GuestFile
     */
    public function setCreatedAt(\DateTime $createdAt): GuestFile
    {
        $this->createdAt = $createdAt;
        return $this;
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
