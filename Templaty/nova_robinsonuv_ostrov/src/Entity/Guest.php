<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @ORM\Entity
 * @HasLifecycleCallbacks()
 * @UniqueEntity(fields="email", message="This email address is already in use")
 */
class Guest implements UserInterface
{
    // base role
    const ROLE_GUEST = 'ROLE_GUEST';
    // player role
    const ROLE_PLAYER = 'ROLE_PLAYER';
    // token valid period
    const TOKEN_VALIDITY = '2 hours';

    /**
     * @var int
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\Email()
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, unique=true, nullable=true)
     */
    private $facebookId;

    /**
     * @var string
     * @Assert\LessThanOrEqual(64)
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @var string
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $token;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenUpdatedAt;

    /**
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $verified = false;

    /**
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean", options={"default"="false"})
     * @var bool
     */
    private $facebookPaired = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $verifiedAt;

    /**
     * @Assert\Type(type="bool")
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $banned = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $bannedAt;

    /**
     * @var array
     * @ORM\Column(type="json", options={"default"="{}"})
     */
    private $data = [];

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;


    /**
     * @param string $facebookId
     * @param string $email
     * @param string $name
     * @return $this
     */
    public static function createFromFbData(string $facebookId, string $email, string $name)
    {
        $user = new self();
        return $user
            ->setFacebookId($facebookId)
            ->setEmail($email)
            ->setName($name)
            ->setVerified(true)
            ->setPassword('password login not enabled before reset');
    }

    // UserInterface -----------

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
    public function getRoles()
    {
        if ($this->isVerified() && !$this->isBanned()) {
            return [self::ROLE_GUEST, self::ROLE_PLAYER];
        }
        return [self::ROLE_GUEST];
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
    public function isEmailLoginAllowed()
    {
        return !$this->getFacebookId() || $this->facebookPaired;
    }

    /**
     * @return bool
     */
    public function isFacebookLoginAllowed()
    {
        return (bool) $this->getFacebookId();
    }

    /**
     * @return bool
     */
    public function isTokenValid()
    {
        if (is_null($this->token)) {
            return false;
        }
        $tokenExpire = $this->tokenUpdatedAt->add(\DateInterval::createFromDateString(self::TOKEN_VALIDITY));
        return new \DateTime() <= $tokenExpire;
    }

    /**
     * @param string $password
     *
     * @return $this;
     *
     * @throws \LogicException user is already stored
     */
    public function setRegistrationPassword(string $password)
    {
        if ($this->getId()) {
            throw new \LogicException('Registration password can be set only to new user');
        }

        return $this->setPassword($password)
                ->regenerateToken();
    }

    /**
     * @param string $password
     *
     * @return $this;
     *
     * @throws \LogicException user email is not confirmed
     */
    public function resetPassword(string $password)
    {
        if (!$this->isVerified()) {
            throw new \LogicException('User must have confirmed email to accept password reset');
        }

        $this->token = null;
        $this->tokenUpdatedAt = null;

        return $this->setPassword($password);
    }

    /**
     * @return $this
     */
    public function verifyEmail()
    {
        if (!$this->verified) {
            $this->verified = true;
            $this->verifiedAt = new \DateTime();
        }
        $this->token = null;
        $this->tokenUpdatedAt = null;
        return $this;
    }

    /**
     * @param array $requestData
     * @return GuestEventLog
     */
    public function createRegistrationLog(array $requestData)
    {
        $log = new GuestEventLog();
        return $log->setGuest($this)
            ->setType(GuestEventLog::TYPE_REGISTRATION)
            ->setData(array_merge($requestData, [
                'email' => $this->email,
                'pass_short' => substr($this->password, 0, 6),
                'token' => $this->token,
            ]));
    }

    /**
     * @param array $requestData
     * @return GuestEventLog
     */
    public function createLoginSuccessLog(array $requestData)
    {
        $log = new GuestEventLog();
        return $log->setGuest($this)
            ->setType(GuestEventLog::TYPE_LOGIN_SUCCESSFUL)
            ->setData(array_merge($requestData, [
                'email' => $this->email,
                'pass_short' => substr($this->password, 0, 6),
            ]));
    }

    /**
     * @param array $requestData
     * @return GuestEventLog
     */
    public function createLoginFailureLog(array $requestData)
    {
        $log = new GuestEventLog();
        return $log->setGuest($this)
            ->setType(GuestEventLog::TYPE_LOGIN_UNSUCCESSFUL)
            ->setData(array_merge($requestData, [
                'email' => $this->email,
                'is_verified' => $this->isVerified(),
                'pass_short' => substr($this->password, 0, 6),
            ]));
    }

    /**
     * @param array $requestData
     * @return GuestEventLog
     */
    public function createPasswordChangeLog(array $requestData)
    {
        $log = new GuestEventLog();
        return $log->setGuest($this)
            ->setType(GuestEventLog::TYPE_PASSWORD_CHANGE)
            ->setData(array_merge($requestData, [
                'email' => $this->email,
                'pass_short' => substr($this->password, 0, 6),
            ]));
    }

    /**
     * @param array $requestData
     * @return GuestEventLog
     */
    public function createAccountVerificationLog(array $requestData)
    {
        $log = new GuestEventLog();
        return $log->setGuest($this)
            ->setType(GuestEventLog::TYPE_PASSWORD_CHANGE)
            ->setData(array_merge($requestData, [
                'email' => $this->email
            ]));
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
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookId
     *
     * @return $this
     */
    public function setFacebookId(string $facebookId)
    {
        $this->facebookId = $facebookId;

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
     * @return mixed
     */
    public function getTokenUpdatedAt()
    {
        return $this->tokenUpdatedAt;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }

    /**
     * @param bool $verified
     * @return $this
     */
    public function setVerified(bool $verified)
    {
        if (empty($this->verifiedAt)) {
            $this->verifiedAt = new \DateTime();
        }
        $this->verified = $verified;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getVerifiedAt()
    {
        return $this->verifiedAt;
    }

    /**
     * @param \DateTime $verifiedAt
     */
    public function setVerifiedAt(\DateTime $verifiedAt): void
    {
        $this->verifiedAt = $verifiedAt;
    }

    /**
     * @return bool
     */
    public function isFacebookPaired(): bool
    {
        return $this->facebookPaired;
    }

    /**
     * @param bool $facebookPaired
     * @return $this
     */
    public function setFacebookPaired(bool $facebookPaired)
    {
        $this->facebookPaired = $facebookPaired;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->banned;
    }

    /**
     * @param bool $banned
     */
    public function setBanned(bool $banned): void
    {
        if (empty($this->bannedAt)) {
            $this->bannedAt = new \DateTime();
        }
        $this->banned = $banned;
    }

    /**
     * @return \DateTime|null
     */
    public function getBannedAt()
    {
        return $this->bannedAt;
    }

    /**
     * @param \DateTime $bannedAt
     */
    public function setBannedAt(\DateTime $bannedAt): void
    {
        $this->bannedAt = $bannedAt;
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
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return $this
     */
    public function setPlainPassword(string $plainPassword)
    {
        $this->plainPassword = $plainPassword;

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
