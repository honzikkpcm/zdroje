<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="email", message="This email address is already in use")
 * @todo: add function to prevent delete default administrator
 */
class Staff implements UserInterface
{
    const TOKEN_VALIDITY = '1 hour';

    // roles
    const
        ROLE_ADMIN = 'ROLE_ADMIN',
        ROLE_EDITOR = 'ROLE_EDITOR';

    /**
     * @var int
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(64)
     * @Assert\Email()
     * @ORM\Column(type="string", length=64, unique=true)
     * @var string
     */
    private $email;

    /**
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(64)
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $name;

    /**
     * @var string
     * @Assert\LessThanOrEqual(256)
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $comment;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getStaffRoles")
     * @ORM\Column(type="string", length=16)
     * @var string
     */
    private $role = self::ROLE_EDITOR;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @Assert\LessThanOrEqual(64)
     * @ORM\Column(type="string", length=64, nullable=true)
     * @var string
     */
    private $token;

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $tokenUpdatedAt;

    /**
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $active = false;

    /**
     * @var array
     * @ORM\Column(type="json", options={"default"="{}"})
     */
    private $data = [];


    // UserInterface -----------

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return [$this->role];
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        //noop
    }

    // / UserInterface -----------


    /**
     * @return bool
     */
    public function isTokenValid()
    {
        if (is_null($this->token)) {
            return false;
        }
        $tokenExpire = $this->tokenUpdatedAt->add(\DateInterval::createFromDateString(self::TOKEN_VALIDITY));
        return $tokenExpire > new \DateTime();
    }

    /**
     * @param string $password
     *
     * @return $this;
     *
     * @throws \LogicException user email is not confirmed
     */
    public function changePassword(string $password, bool $invalidateToken)
    {
        if ($invalidateToken) {
            $this->token = null;
            $this->tokenUpdatedAt = null;
        }
        return $this->setPassword($password);
    }

    /**
     * Creates and updates token used in change confirmation.
     * @return $this
     */
    public function regenerateToken()
    {
        $this->token = hash('sha256', $this->getEmail().time().uniqid());
        $this->tokenUpdatedAt = new \DateTime();
        return $this;
    }

    /**
     * @param array $requestData
     * @return StaffEventLog
     */
    public function createLoginSuccessLog(array $requestData)
    {
        $log = new StaffEventLog();
        $log->setStaff($this);
        $log->setAction(StaffEventLog::ACTION_LOGIN_SUCCESSFUL);
        $log->setResource(Staff::class);
        $log->setData(array_merge($requestData, [
                'email' => $this->email,
                'pass_short' => substr($this->password, 0, 6),
            ]));
        return $log;
    }

    /**
     * @param array $requestData
     * @return StaffEventLog
     */
    public function createLoginFailureLog(array $requestData)
    {
        $log = new StaffEventLog();
        $log->setStaff($this);
        $log->setAction(StaffEventLog::ACTION_LOGIN_UNSUCCESSFUL);
        $log->setResource(Staff::class);
        $log->setData(array_merge($requestData, [
            'email' => $this->email,
            'pass_short' => substr($this->password, 0, 6),
        ]));
        return $log;
    }

    /**
     * @param array $requestData
     * @return StaffEventLog
     */
    public function createPasswordChangeLog(array $requestData)
    {

        $log = new StaffEventLog();
        $log->setStaff($this);
        $log->setAction(StaffEventLog::ACTION_PASSWORD_CHANGED);
        $log->setResource(Staff::class);
        $log->setData(array_merge($requestData, [
            'email' => $this->email,
            'pass_short' => substr($this->password, 0, 6),
        ]));
        return $log;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTokenUpdatedAt()
    {
        return $this->tokenUpdatedAt;
    }

    /**
     * @param \DateTime $tokenUpdatedAt
     *
     * @return $this
     */
    public function setTokenUpdatedAt(\DateTime $tokenUpdatedAt)
    {
        $this->tokenUpdatedAt = $tokenUpdatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive(bool $active)
    {
        $this->active = $active;

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

    // internal --------------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    public static function getStaffRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
        ];
    }

}
