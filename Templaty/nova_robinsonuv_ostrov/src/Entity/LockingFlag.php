<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * Class LockingFlag
 * @package App\Entity
 * @ORM\Entity()
 * @HasLifecycleCallbacks()
 */
class LockingFlag
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
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    private $resource = '';

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return LockingFlag
     */
    public function setKey(string $key): LockingFlag
    {
        $this->key = $key;
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
     * @return LockingFlag
     */
    public function setResource(string $resource): LockingFlag
    {
        $this->resource = $resource;
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
     * @return LockingFlag
     */
    public function setCreatedAt(\DateTime $createdAt): LockingFlag
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