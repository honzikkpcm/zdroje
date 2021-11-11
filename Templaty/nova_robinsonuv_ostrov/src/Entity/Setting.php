<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Setting
 * @package App\Entity
 * @ORM\Entity()
 */
class Setting
{
    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(64)
     * @ORM\Id;
     * @ORM\Column(type="string", length=64, unique=true)
     * @var string
     */
    private $key;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $value = '';

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(16)
     * @ORM\Column(type="string", length=16)
     * @var string
     */
    private $type = 'string';

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
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

}