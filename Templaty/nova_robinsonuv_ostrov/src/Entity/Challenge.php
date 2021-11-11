<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Challenge
 * @package App\Entity
 * @ORM\Entity()
 * @HasLifecycleCallbacks()
 * @UniqueEntity(fields="urlcode", message="Urlcode must be unique.")
 */
class Challenge
{
    // types
    const
        TYPE_STANDARD = 'standard',
        TYPE_BONUS = 'bonus';

    /**
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Competition")
     * @ORM\JoinColumn(name="competition_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Competition
     */
    private $competition;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(16)
     * @Assert\Choice(callback="getTypes")
     * @ORM\Column(type="string", length=16)
     * @var string
     */
    private $type = self::TYPE_STANDARD;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    private $name = '';

    /**
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128, nullable=true, unique=true)
     * @var string
     */
    private $urlcode = '';

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     * @var string
     */
    private $urlcodeHash;

    /**
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string
     */
    private $bonusAnswers = '';

    /**
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(type="smallint", options={"default"="0"})
     * @var int
     */
    private $bonusMaxTime = 0;

    /**
     * @Assert\Type(type="string")
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description = '';

    /**
     * @Assert\File(mimeTypes={"image/jpeg"})
     * @ORM\Column(type="string", length=256, nullable=true)
     * @var string
     */
    private $image = '';

    /**
     * @Assert\NotBlank()
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $validFrom;

    /**
     * @Assert\NotBlank()
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $validTo;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(type="smallint", options={"default"="0"})
     * @var int
     */
    private $number = 0;

    /**
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $active = true;

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Many Users have Many Groups.
     * @ORM\OneToMany(targetEntity="ChallengeTask", mappedBy="challenge")
     * @ORM\OrderBy({"sorting"="ASC"})
     * @var ArrayCollection
     */
    private $tasks;

    /**
     */
    public function __construct() {
        $this->tasks = new ArrayCollection();
    }

    /**
     * @param GuestChallenge $guestChallenge
     */
    public function rate(GuestChallenge $guestChallenge) {
        if (!$guestChallenge->isFinished()) {
            $guestChallenge->setFinishedAt(new \DateTime());
        }

        $score = 0;
        /** @var ChallengeTask $task */
        foreach($this->tasks as $task) {
            $answer = $guestChallenge->getTaskAnswer($task->getId());
            // answer can be empty
            if (!empty($answer)) {
                $score += $task->rate($answer);
            }
        }
        if ($this->bonusMaxTime) {
            $diff = $guestChallenge->getFinishedAt()->getTimestamp() - $guestChallenge->getCreatedAt()->getTimestamp();
            $score = ChallengeTask::score($diff, $this->bonusMaxTime, $score);
        }

        $guestChallenge->setScore($score);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $now = new \DateTime();
        return $this->validFrom <= $now && $this->validTo >= $now;
    }

    /**
     * @return bool|\DateInterval
     */
    public function endsIn()
    {
        $now = new \DateTime();
        return $this->validTo->diff($now);
    }

    /**
     * @return int
     */
    public function getMaxScore()
    {
        return array_sum(array_map(function(ChallengeTask $task) {
            return $task->getScore();
        }, $this->tasks->toArray()));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Competition
     */
    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     */
    public function setCompetition(Competition $competition): void
    {
        $this->competition = $competition;
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
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrlcode(): string
    {
        return $this->urlcode;
    }

    /**
     * @param string $urlcode
     */
    public function setUrlcode(string $urlcode): void
    {
        $this->urlcode = \App\Utils\Strings::webalize($urlcode);
        $this->urlcodeHash = sha1($this->urlcode);
    }

    /**
     * @return string
     */
    public function getBonusAnswers(): string
    {
        return $this->bonusAnswers ?? '';
    }

    /**
     * @param string $bonusAnswers
     */
    public function setBonusAnswers(string $bonusAnswers): void
    {
        $this->bonusAnswers = $bonusAnswers;
    }

    /**
     * @return int
     */
    public function getBonusMaxTime(): int
    {
        return $this->bonusMaxTime;
    }

    /**
     * @param int $bonusMaxTime
     */
    public function setBonusMaxTime(int $bonusMaxTime): void
    {
        $this->bonusMaxTime = $bonusMaxTime;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime $validFrom
     */
    public function setValidFrom(\DateTime $validFrom): void
    {
        $this->validFrom = $validFrom;
    }

    /**
     * @return \DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param \DateTime $validTo
     */
    public function setValidTo(\DateTime $validTo): void
    {
        $this->validTo = $validTo;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
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
     * @return ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    // pre/post persist ------------------------------------------------------------------------------------------------

    /**
     * @PrePersist()
     */
    public function doUrlcodePrePersist(): void
    {
        if (empty($this->urlcode)) {
            $this->urlcode = \App\Utils\Strings::webalize($this->name);
            $this->urlcodeHash = sha1($this->urlcode);
        }
    }

    /**
     * @PrePersist()
     */
    public function doCreatedAtPrePersist(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    // internal --------------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_STANDARD,
            self::TYPE_BONUS,
        ];
    }
}
