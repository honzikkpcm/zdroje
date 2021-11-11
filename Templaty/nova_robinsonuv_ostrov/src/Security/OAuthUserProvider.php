<?php
namespace App\Security;

use App\Entity\Guest;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class OAuthUserProvider
 */
class OAuthUserProvider implements OAuthAwareUserProviderInterface
{
    /**
     * @var ManagerRegistry;
     */
    private $doctrine;

    /**
     * @var RegistrationHelper;
     */
    private $registrationHelper;

    /**
     * OAuthUserProvider constructor.
     * @param ManagerRegistry $doctrine
     * @param RegistrationHelper $registrationHelper
     */
    public function __construct(ManagerRegistry $doctrine, RegistrationHelper $registrationHelper)
    {
        $this->doctrine = $doctrine;
        $this->registrationHelper = $registrationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $manager = $this->doctrine->getManager();
        $socialId = $response->getUsername();
        /** @var Guest $user */
        $user = $manager->getRepository(Guest::class)
            ->findOneBy(['facebookId' => $socialId]);
        if ($user) {
            return $user;
        } else {
            if ($response->getRealName()) {
                $name = $response->getRealName();
            } elseif ($response->getFirstName() && $response->getLastName()) {
                $name = $response->getFirstName() . ' ' . $response->getLastName();
            } elseif ($response->getNickname()) {
                $name = $response->getNickname();
            } else {
                $name = $response->getEmail();
            }
            $data = [
                'facebookId' => $socialId,
                'name' => $name,
                'email' => $response->getEmail(),
                'token' => $response->getAccessToken()
            ];
            $this->registrationHelper->setPendingOauthUserData($data);
            throw new UsernameNotFoundException('Not linked');
        }
    }
}
