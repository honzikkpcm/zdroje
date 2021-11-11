<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChallengeTask
 * @package App\Entity
 * @ORM\Entity()
 */
class ChallengeTask
{
    // types
    const
        TYPE_QUIZ_ABCD = 'quiz-abcd',
        TYPE_QUIZ_PHOTO = 'quiz-photo',
        TYPE_ORDER_PHOTOS = 'order-photos',
        TYPE_SELECT_PHOTO = 'select-photo',
        TYPE_WORD = 'word',
        TYPE_PHOTO = 'photo',
        TYPE_VIDEO = 'video',
        TYPE_GUESS = 'guess';

    // data
    const
        DATA_IMAGE = 'image',
        DATA_SORTABLE = 'sortable',
        DATA_ANSWER = 'answer',
        DATA_ANSWERS = 'answers',
        DATA_VIDEO = 'video';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Challenge", inversedBy="tasks")
     * @ORM\JoinColumn(name="challenge_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Challenge
     */
    private $challenge;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(16)
     * @Assert\Choice(callback="getTypes")
     * @ORM\Column(type="string", length=16)
     * @var string
     */
    private $type;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(512)
     * @ORM\Column(type="string", length=512)
     * @var string
     */
    private $caption;

    /**
     * @Assert\Type(type="string")
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description;

    /**
     * @Assert\Type(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(type="smallint", options={"default"="0"})
     * @var int
     */
    private $score = 0;

    /**
     * @ORM\Column(type="json", options={"default"="{}"})
     * @var array
     */
    private $data = [];

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(type="integer", options={"default"="0"})
     * @var int
     */
    private $sorting = 0;

    /**
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean", options={"default"="true"})
     * @var bool
     */
    private $active = true;


    /**
     * Computes time quiz score using pattern
     *
     * @param int $time
     * @param int $maxTime
     * @param int $guestScore
     * @return int
     */
    public static function score(int $time, int $maxTime, int $guestScore): int
    {
        if (($time < 1) || ($maxTime < 1) || ($guestScore < 1))
            throw new \InvalidArgumentException('Invalid argument has been entered');

        $partialTime = ($time / $maxTime);

        if ($partialTime >= 1)
            return 0;

        return $guestScore - (int)round($partialTime * $guestScore);
    }

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_QUIZ_ABCD,
            self::TYPE_QUIZ_PHOTO,
            self::TYPE_ORDER_PHOTOS,
            self::TYPE_SELECT_PHOTO,
            self::TYPE_WORD,
            self::TYPE_PHOTO,
            self::TYPE_VIDEO,
            self::TYPE_GUESS,
        ];
    }
    
    /**
     * @param mixed $answer
     * @return int
     */
    public function rate($answer) {
        if (is_null($answer))
            throw new \InvalidArgumentException('Answer can not be null');

        if ($this->isActive() && $this->getScore()) {
            switch ($this->getType()) {
                case self::TYPE_QUIZ_ABCD:
                case self::TYPE_QUIZ_PHOTO:
                case self::TYPE_SELECT_PHOTO:
                case self::TYPE_GUESS:
                    $answer = (array)$answer;
                    $answers = [];

                    foreach ($this->data[self::DATA_ANSWERS] as $answerItem) {
                        if ($answerItem['checked']) {
                            $answers[] = $answerItem['id'];
                        }
                    }

                    $diff = array_diff($answer, $answers);

                    if (empty($diff)) {
                        return $this->getScore();
                    }
                    break;

                case self::TYPE_ORDER_PHOTOS:
                    $answer = (array)$answer;
                    $answers = [];

                    foreach ($this->data[self::DATA_SORTABLE] as $answerItem) {
                        $answers[] = $answerItem['id'];
                    }

                    if ($answer == $answers) {
                        return $this->getScore();
                    }
                    break;

                case self::TYPE_WORD:
                    $answers = explode(';', $this->data[self::DATA_ANSWER]);
                    array_walk($answers, function(&$item){
                        $item = trim($item);
                    });

                    if (in_array($answer, $answers)) {
                        return $this->getScore();
                    }
                    break;

                case self::TYPE_PHOTO:
                case self::TYPE_VIDEO:
                    if ($answer === GuestFile::STATUS_APPROVED) {
                        return $this->getScore();
                    }
                    break;

                default:
                    throw new \UnexpectedValueException('Unknown '.$this->getType().' quiz type.');
            }
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function getCaption(): string
    {
        return $this->caption;
    }

    /**
     * @param string $caption
     */
    public function setCaption(string $caption): void
    {
        $this->caption = $caption;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
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
     * @return int
     */
    public function getSorting(): int
    {
        return $this->sorting;
    }

    /**
     * @param int $sorting
     */
    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
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

}
