<?php

namespace App\Security;

use App\Entity\Staff;
use App\Utils\RegistryHelper;
use App\Utils\RequestDumper;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Supports form login with username and password by intercepting staff_auth_login_check route.
 * Unauthenticated users are redirected to staff_auth_login route,
 * Guard redirects users to their original location after successful login.
 */
class BackendLoginAuthenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * BackendLoginAuthenticator constructor.
     * @param RouterInterface $router
     * @param UserPasswordEncoderInterface $encoder
     * @param ManagerRegistry $doctrine
     */
    public function __construct(RouterInterface $router, UserPasswordEncoderInterface $encoder, ManagerRegistry $doctrine)
    {
        $this->router = $router;
        $this->encoder = $encoder;
        $this->doctrine = $doctrine;
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') == 'staff_login_check';
    }

    /**
     * @param Request $request
     * @param AuthenticationException $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->getLoginUrl();

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // if the user hits a secure page and start() was called, this was
        // the URL they were on, and probably where you want to redirect to
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        $this->removeTargetPath($request->getSession(), $providerKey);
        if (!$targetPath) {
            $targetPath = $this->router->generate('dashboard');
        }
        $user = $token->getUser();
        if ($user instanceof Staff) {
            $requestData = RequestDumper::dump($request, [
                RequestDumper::URL,
                RequestDumper::USER_INFO,
            ]);
            RegistryHelper::store([$user->createLoginSuccessLog($requestData)], $this->doctrine);
        }
        return new RedirectResponse($targetPath);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->getSession() instanceof SessionInterface) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        $user = $exception->getToken()->getUser();
        // better type safe then sorry
        if ($user instanceof Staff) {
            $requestData = RequestDumper::dump($request, [
                RequestDumper::URL,
                RequestDumper::USER_INFO,
                RequestDumper::FORWARD_HEADERS,
            ]);
            RegistryHelper::store([$user->createLoginFailureLog($requestData)], $this->doctrine);
        }

        return new RedirectResponse($this->getLoginUrl());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getCredentials(Request $request)
    {
        $email = $request->request->get('email');
        $request->getSession()->set(Security::LAST_USERNAME, $email);
        $password = $request->request->get('password');

        return [
            'email' => $email,
            'password' => $password,
        ];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $email = $credentials['email'];

        return $userProvider->loadUserByUsername($email);
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $plainPassword = $credentials['password'];
        if ($this->encoder->isPasswordValid($user, $plainPassword)) {
            return true;
        }

        throw new BadCredentialsException();
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * @return string
     */
    private function getLoginUrl()
    {
        return $this->router->generate('staff_login');
    }
}
