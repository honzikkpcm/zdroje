<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 */
class GuestEventLog
{
    const TYPE_REGISTRATION = 'registration';
    const TYPE_ACCOUNT_VERIFICATION = 'account verification';
    const TYPE_BAN = 'ban';
    const TYPE_PASSWORD_RESET = 'password reset';
    const TYPE_PASSWORD_CHANGE = 'password change';
    const TYPE_LOGIN_SUCCESSFUL = 'login successful';
    const TYPE_LOGIN_UNSUCCESSFUL = 'login unsuccessful';
    const TYPE_CHALLENGE_FINISHED = 'challenge finished';

    /**
     * @var int
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Guest")
     * @ORM\JoinColumn(name="guest_id", referencedColumnName="id")
     */
    private $guest;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $type;

    /**
     * @var array
     * @ORM\Column(type="json", options={"default"="{}"})
     */
    private $data = [];

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Guest
     */
    public function getGuest()
    {
        return $this->guest;
    }

    /**
     * @param Guest $guest
     *
     * @return $this
     */
    public function setGuest(Guest $guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
