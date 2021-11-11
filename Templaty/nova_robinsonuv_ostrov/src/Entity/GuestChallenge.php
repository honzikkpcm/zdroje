<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * Class GuestChallenge
 * @package App\Entity
 * @ORM\Entity()
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(columns={"guest_id", "challenge_id"})}
 * )
 * @HasLifecycleCallbacks()
 */
class GuestChallenge
{
    /** @const string */
    const DATA_ANSWERS = 'answers';

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
     * @ORM\ManyToOne(targetEntity="Challenge")
     * @ORM\JoinColumn(name="challenge_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Challenge
     */
    private $challenge;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(type="integer", options={"default"="0"})
     * @var int
     */
    private $score = 0;

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
     * @Assert\DateTime()
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $finishedAt;

    /**
     * @return bool
     */
    public function isFinished()
    {
        return !is_null($this->finishedAt);
    }

    /**
     * @param int $taskId
     * @return mixed
     */
    public function getTaskAnswer($taskId)
    {
        return $this->data[self::DATA_ANSWERS][$taskId] ?? '';
    }

    /**
     * @param int $taskId
     * @param mixed $data
     * @return GuestChallenge
     */
    public function setTaskAnswer($taskId, $data)
    {
        $this->data[self::DATA_ANSWERS][$taskId] = $data;
        return $this;
    }

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
     */
    public function setGuest(Guest $guest): void
    {
        $this->guest = $guest;
    }

    /**
     * @return Challenge
     */
    public function getChallenge(): Challenge
    {
        return $this->challenge;
    }

    /**
     * @param Challenge $challenge
     */
    public function setChallenge(Challenge $challenge): void
    {
        $this->challenge = $challenge;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     */
    public function setScore(int $score): void
    {
        $this->score = $score;
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
     */
    public function setData(array $data): void
    {
        $this->data = $data;
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

    /**
     * @return \DateTime|null
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTime $finishedAt
     */
    public function setFinishedAt(\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
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
