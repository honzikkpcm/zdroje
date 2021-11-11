<?php
/**
 * Created by PhpStorm.
 * User: rum
 * Date: 19.2.18
 * Time: 11:26
 */

namespace App\Security;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Helper methods for registering new accounts created by oauth login flow
 */
class RegistrationHelper
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var GuardAuthenticatorHandler
     */
    private $authenticator;

    private $userData = null;

    /**
     * OAuthRegistrationHelper constructor.
     * @param RequestStack $requestStack
     * @param GuardAuthenticatorHandler $authenticator
     */
    public function __construct(RequestStack $requestStack, GuardAuthenticatorHandler $authenticator)
    {
        $this->requestStack = $requestStack;
        $this->authenticator = $authenticator;
    }


    /**
     * Sets data associated with pending oAuth login
     * @param array $data
     */
    public function setPendingOauthUserData(array $data) :void
    {
        $this->requestStack->getCurrentRequest()->getSession()->set('oauth.facebook.data', $data);
    }

    /**
     * Returns data associated with pending oAuth login
     *
     * @throws \RuntimeException user data was not stored
     * @return array
     */
    public function getPendingOauthUserData()
    {
        if (is_null($this->userData)) {
            $token = $this->getStoredToken();
            $accessToken = $token->getRawToken()['access_token'];
            $data = (array)$this->requestStack->getCurrentRequest()->getSession()
                ->get('oauth.facebook.data');
            if (!isset($data['token']) || $data['token'] != $accessToken) {
                throw new \RuntimeException('Token mismatch in data: ' . ($data['token'] ?? '') . ' x ' . $accessToken);
            }
            $this->userData = $data;
        }
        return $this->userData;
    }

    /**
     * Creates oauth token for user authentication outside normal login flow.
     *
     * @param UserInterface $user
     */
    public function authenticateOauthAccount(UserInterface $user)
    {
        $token = $this->getStoredToken();

        $token = new OAuthToken($token->getRawToken(), $user->getRoles());
        $token->setResourceOwnerName($token->getResourceOwnerName());
        $token->setUser($user);
        $token->setAuthenticated(true);
        $this->authenticator->authenticateWithToken($token, $this->requestStack->getCurrentRequest());
    }

    /**
     * Creates oauth token for user authentication outside normal login flow.
     *
     * @param UserInterface $user
     */
    public function authenticateEmailAccount(UserInterface $user)
    {
        $token = new PostAuthenticationGuardToken($user, 'main', $user->getRoles());
        $token->setAuthenticated(true);
        $this->authenticator->authenticateWithToken($token, $this->requestStack->getCurrentRequest());
    }

    /**
     * @return OAuthToken
     */
    private function getStoredToken()
    {
        $error = $this->requestStack->getCurrentRequest()->getSession()
            ->get('_security.last_error');
        if (!$error instanceof AuthenticationException) {
            throw new \RuntimeException('Cannot retrieve fb data.', 0, $error instanceof \Exception ? $error : null);
        }
        $token = $error->getToken();
        if (!$token instanceof OAuthToken) {
            throw new \RuntimeException('Token mismatch: ' . get_class($token), 0, $error instanceof \Exception ? $error : null);
        }
        return $token;
    }
}
