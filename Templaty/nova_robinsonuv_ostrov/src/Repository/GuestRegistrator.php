<?php

namespace App\Repository;

use App\Entity\Guest;
use App\Entity\GuestEventLog;
use App\Orm\Registry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

class GuestRegistrator
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UserPasswordEncoderInterface;
     */
    private $encoder;

    public function __construct(Registry $registry, UserPasswordEncoderInterface $encoder)
    {
        $this->registry = $registry;
        $this->encoder = $encoder;
    }

    /**
     * @param Guest $user
     */
    public function register(Guest $user, Request $request)
    {
        $manager = $this->registry->getManager(Guest::class);

        $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setRegistrationPassword($password);
        $log = new GuestEventLog();
        $log->setGuest($user)
            ->setType(GuestEventLog::TYPE_REGISTRATION)
            ->setData([
                'ip' => $request->getClientIp(),
                'userAgent' => $request->headers->get('User-Agent'),
                'url' => $request->getSchemeAndHttpHost().$request->getRequestUri(),
                'email' => $user->getEmail(),
                'passShort' => substr($password, 0, 6),
                'token' => $user->getToken(),
            ]);
        $manager->persist($log);
        $manager->persist($user);
        $manager->flush();
    }
}
