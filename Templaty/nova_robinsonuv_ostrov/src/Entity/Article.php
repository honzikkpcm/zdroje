<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * Class Article
 * @package App\Entity
 * @ORM\Entity()
 * @HasLifecycleCallbacks()
 * @UniqueEntity(fields="urlcode", message="Urlcode must be unique.")
 */
class Article
{
    /**
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    private $name = '';

    /**
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128, unique=true)
     * @var string
     */
    private $urlcode = '';

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     * @var string
     */
    private $urlcodeHash;

    /**
     * @Assert\LessThanOrEqual(512)
     * @ORM\Column(type="string", length=512, nullable=true)
     * @var string
     */
    private $seoDescription = '';

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     * @var string
     */
    private $content = '';

    /**
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean", options={"default"="true"})
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function getSeoDescription(): string
    {
        return $this->seoDescription;
    }

    /**
     * @param string $seoDescription
     */
    public function setSeoDescription(string $seoDescription): void
    {
        $this->seoDescription = $seoDescription;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
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
}
