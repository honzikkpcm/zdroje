<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * Class Message
 * @package App\Entity
 * @ORM\Entity()
 * @HasLifecycleCallbacks()
 */
class Message
{
    // message default types
    const
        TYPE_STAFF_REGISTRATION = 'staff_registration',
        TYPE_STAFF_RESET_PASSWORD = 'staff_reset_password',
        TYPE_GUEST_REGISTRATION = 'guest_registration',
        TYPE_GUEST_VERIFY_REGISTRATION = 'guest_verify_registration',
        TYPE_GUEST_RESET_PASSWORD = 'guest_reset_password',
        TYPE_GUEST_PASSWORD_CHANGED = 'guest_password_changed',
        TYPE_GUEST_BANNED = 'guest_banned',
        TYPE_GUEST_NOT_CLOSED_CHALLENGES = 'guest_not_closed_challenges';

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
    private $driver;

    /**
     * Used to identify local or remote template
     *
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(32)
     * @ORM\Column(type="string", length=32)
     * @var string
     */
    private $type;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(256)
     * @ORM\Column(type="string", length=256)
     * @var string
     */
    private $subject;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(128)
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    private $fromAddress;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(512)
     * @ORM\Column(type="string", length=512)
     * @var string
     * <code>
     * $to = 'foo@bar.com';
     * $to = 'Foo Bar <foo@bar.com>';
     * $to = 'foo@bar.com, Foo Bar <foo@bar.com>';
     * </code>
     */
    private $toAddress;

    /**
     * @Assert\LessThanOrEqual(512)
     * @ORM\Column(type="string", length=512)
     * @var string
     * <code>
     * $to = 'foo@bar.com';
     * $to = 'Foo Bar <foo@bar.com>';
     * $to = 'foo@bar.com, Foo Bar <foo@bar.com>';
     * </code>
     */
    private $cc = '';

    /**
     * HTML body
     *
     * @ORM\Column(type="text")
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(type="json", options={"default"="{}"})
     * @var array
     */
    private $params = [];

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean", options={"default"="false"})
     * @var bool
     */
    private $sent = false;

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $sentAt;

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
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     * @return Message
     */
    public function setDriver(string $driver): Message
    {
        $this->driver = $driver;
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
     * @return Message
     */
    public function setType(string $type): Message
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Message
     */
    public function setSubject(string $subject): Message
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->fromAddress;
    }

    /**
     * @param string $fromAddress
     * @return Message
     */
    public function setFrom(string $from): Message
    {
        $this->fromAddress = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->toAddress;
    }

    /**
     * @param string $to
     * @return Message
     */
    public function setTo(string $to): Message
    {
        $this->toAddress = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getCc(): string
    {
        return $this->cc;
    }

    /**
     * @param string $cc
     */
    public function setCc(string $cc): void
    {
        $this->cc = $cc;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Message
     */
    public function setBody(string $body): Message
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return Message
     */
    public function setParams(array $params): Message
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * @param bool $sent
     */
    public function setSent(bool $sent): void
    {
        $this->sent = $sent;
    }

    /**
     * @return \DateTime
     */
    public function getSentAt(): \DateTime
    {
        return $this->sentAt;
    }

    /**
     * @param \DateTime $sentAt
     */
    public function setSentAt(\DateTime $sentAt): void
    {
        $this->sentAt = $sentAt;
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
