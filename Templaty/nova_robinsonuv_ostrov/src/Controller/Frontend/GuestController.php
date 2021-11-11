<?php
namespace App\Controller\Frontend;

use App\Entity\Guest;
use App\Entity\Message;
use App\Service\MessengerInterface;
use App\Utils\RegistryHelper;
use App\Utils\RequestDumper;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Security\RegistrationHelper;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Auth actions and profile action for app users
 */
class GuestController extends Controller
{
    /**
     * Api for current user data.
     *
     * @Route(
     *     name="guest_profile",
     *     path="/guest/profile",
     * )
     *
     * @param Request $request
     * @param UserInterface $user
     * @return Response
     */
    public function profile(Request $request, UserInterface $user = null): Response
    {
        // get all challenges which has been opened
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('SELECT c.id,name,score,urlcode,valid_from,valid_to,finished_at '
            .'FROM challenge c '
            .'LEFT JOIN guest_challenge gc ON gc.challenge_id=c.id '
            .'WHERE valid_from <= NOW() AND (guest_id=? OR guest_id IS NULL) '
            .'ORDER BY valid_to ASC');
        $stmt->execute([$user->getId()]);
        $challenges = $stmt->fetchAll();

        return $this->render('Frontend/Guest/profile.html.twig', [
            'challenges' => $challenges,
        ]);
    }

    /**
     * Api for current user data.
     *
     * @Route(
     *     name="guest_resend_verification_email",
     *     path="/guest/resend-verification-email",
     *     methods="POST"
     * )
     *
     * @param UserInterface $user
     * @param ManagerRegistry $registry
     * @param MessengerInterface $messenger
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resendVerificationEmail(
        UserInterface $user,
        ManagerRegistry $registry,
        MessengerInterface $messenger,
        TranslatorInterface $t)
    {
        if ($user->isVerified()) {
            $this->addFlash('notice', $t->trans('Your account was already verified.'));
        } else {
            $user->regenerateToken();
            RegistryHelper::store([$user], $registry);

            $message = (new Message())
                ->setType(Message::TYPE_GUEST_VERIFY_REGISTRATION)
                ->setTo($user->getEmail())
                ->setSubject($t->trans('Robinson verify account'))
                ->setParams([
                    'url' => $this->generateUrl('guest_verify_account', ['token'=> $user->getToken()]),
                ]);
            $messenger->send($message);

            $this->addFlash(
                'notice',
                $t->trans('We\'ll send you instructions how to finish verification process. If you do not receive an email from us, please check your spam folder.'));
        }

        return $this->redirectToRoute('guest_profile');
    }

    /**
     * Processes user data from registration form.
     * Successful registration is redirected to confirmation page.
     * Invalid form is rendered with errors.
     *
     * @Route(
     *     name="guest_register",
     *     path="/guest/register",
     * )
     *
     * @param Request $request
     * @param ManagerRegistry $registry
     * @param UserPasswordEncoderInterface $encoder
     * @param MessengerInterface $messenger
     * @param TranslatorInterface $t
     * @param UserInterface $user
     * @return RedirectResponse|Response
     */
    public function register(
        Request $request,
        ManagerRegistry $registry,
        UserPasswordEncoderInterface $encoder,
        MessengerInterface $messenger,
        TranslatorInterface $t,
        UserInterface $user = null
    ) {
        // test if user is logged in
        if ($user instanceof Guest) {
            return $this->redirectToRoute('guest_profile');
        }

        $form = $this->createRegistrationForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setRegistrationPassword($password);
            $log = $user->createRegistrationLog($this->dumpRequest($request));
            RegistryHelper::store([$log, $user], $registry);

            $message = (new Message())
                ->setType(Message::TYPE_GUEST_REGISTRATION)
                ->setTo($user->getName() .' <' .$user->getEmail() .'>')
                ->setSubject($t->trans('Robinson account registered'))
                ->setParams([
                    'url' => $this->generateUrl('guest_verify_account', ['token'=> $user->getToken()]),
                ]);
            $messenger->send($message);

            $this->addFlash(
                'success',
                $t->trans('Thank you for creating new account. We\'ll send you instructions on how to finish registration process. If you do not receive an email from us, please check your spam folder.')
            );
            return $this->redirectToRoute('guest_login');
        }

        return $this->render(
            'Frontend/Guest/register.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Creates new account for users logged in by facebook.
     * Invalid form is rendered with errors.
     *
     * @Route(
     *     name="guest_connect_facebook",
     *     path="/guest/connect-facebook",
     * )
     *
     * @param Request $request
     * @param ManagerRegistry $registry
     * @param FormFactoryInterface $formFactory
     * @param UserPasswordEncoderInterface $encoder
     * @param RegistrationHelper $registrationHelper
     * @param TranslatorInterface $t
     * @param LoggerInterface $logger
     * @return RedirectResponse|Response
     */
    public function connectFacebook(
        Request $request,
        ManagerRegistry $registry,
        FormFactoryInterface $formFactory,
        UserPasswordEncoderInterface $encoder,
        RegistrationHelper $registrationHelper,
        TranslatorInterface $t,
        LoggerInterface $logger
    ) {
        try {
            $fbInfo = $registrationHelper->getPendingOauthUserData();
            $email = $fbInfo['email'];
            if (!$email) {
                throw new \RuntimeException('Email missing in fb data');
            }
        } catch (\RuntimeException $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            return $this->redirectToRoute('guest_login');
        }

        /** @var Guest $user */
        $user = $registry->getManager()
            ->getRepository(Guest::class)
            ->findOneBy(['email' => $email]);

        if ($user) {
            return $this->handleAccountPairing($request, $registrationHelper, $user, $encoder, $formFactory, $registry, $t);
        } else {
            return $this->handleFbAccountRegistration($request, $registrationHelper, $formFactory, $registry, $t);
        }
    }

    /**
     * @Route(
     *     name="guest_completed",
     *     path="/guest/completed",
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function completed(Request $request)
    {
        return $this->render('Frontend/Guest/completed.html.twig');
    }

    /**
     * Shows login form.
     *
     * @Route(
     *     name="guest_login",
     *     path="/guest/login",
     * )
     *
     * @param Request $request
     * @param AuthenticationUtils $authHelper
     * @param FormFactoryInterface $formFactory
     * @param UserInterface $user
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function login(
        Request $request,
        AuthenticationUtils $authHelper,
        FormFactoryInterface $formFactory,
        UserInterface $user = null,
        TranslatorInterface $t
    ){
        // test if user is logged in
        if ($user instanceof Guest) {
            return $this->redirectToRoute('guest_profile');
        }

        // show  login form then
        $form = $formFactory
            ->createNamed(null, FormType::class, [
                'email' => $authHelper->getLastUsername(),
            ], [
                'action' => $this->generateUrl('guest_login_check')
            ])
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('Login', SubmitType::class, array('label' => 'Login'));

        $error = $authHelper->getLastAuthenticationError();

        if (!empty($error)) {
            $this->addFlash('warning', $t->trans($error->getMessageKey(), $error->getMessageData()));
        }

        return $this->render('Frontend/Guest/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Allows user to change account password.
     * User must be logged in.
     * Old user password must be provided.
     * New password must be different then old one.
     * After successful change, user is logged out.
     *
     * @Route(
     *     name="guest_change_password",
     *     path="/guest/change-password",
     * )
     *
     * @param Request $request
     * @param UserInterface $user
     * @param UserPasswordEncoderInterface $encoder
     * @param ManagerRegistry $registry
     * @param MessengerInterface $messenger
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePassword(
        Request $request,
        UserInterface $user,
        UserPasswordEncoderInterface $encoder,
        ManagerRegistry $registry,
        MessengerInterface $messenger,
        TranslatorInterface $t
    ) {
        $form = $this->createFormBuilder([])
            ->add('currentPassword', PasswordType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'New password','required' => true],
                'second_options' => ['label' => 'Confirm new password','required' => true],
                'constraints' => [new NotBlank()]
            ])
            ->add('Change', SubmitType::class, ['label' => 'Change'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['newPassword'] == $data['currentPassword']) {
                $form->addError(new FormError($t->trans('New password can not be same as old password')));
            } elseif (!$encoder->isPasswordValid($user, $data['currentPassword'])) {
                $form->addError(new FormError($t->trans('Current password is not valid.')));
                RegistryHelper::store([
                    $user->createLoginFailureLog($this->dumpRequest($request))
                ], $registry);
            } else {
                $user->setPassword($encoder->encodePassword($user, $data['newPassword']));
                $log = $user->createPasswordChangeLog($this->dumpRequest($request));
                RegistryHelper::store([$log, $user], $registry);

                $message = (new Message())
                    ->setType(Message::TYPE_GUEST_PASSWORD_CHANGED)
                    ->setSubject($t->trans('Password changed'))
                    ->setTo($user->getEmail())
                    ->setParams([
                        'url' => $this->generateUrl('guest_request_password_reset'),
                    ]);
                $messenger->send($message);

                $this->addFlash(
                    'success',
                    $t->trans('Your password was successfully changed.')
                );
                return $this->redirectToRoute('guest_profile');
            }
        }

        return $this->render(
            'Frontend/Guest/change_password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * Allows user to change password after presenting valid reset token.
     * User can be anonymous.
     *
     * @Route(
     *     name="guest_reset_password",
     *     path="/guest/reset-password/{token}",
     * )
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPassword(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        ManagerRegistry $registry,
        TranslatorInterface $t)
    {
        $token = $request->attributes->get('token');
        $repo = $registry->getManager()->getRepository(Guest::class);
        /** @var Guest $user */
        $user = $repo->findOneBy(['token' => $token]);
        if (!$user || !$user->isTokenValid()) {
            $this->addFlash('danger', $t->trans('Url is not valid. Please generate new reset token'));
            return $this->render('Frontend/Guest/reset_password.html.twig');
        }
        if (!$user->isEmailLoginAllowed()) {
            $this->addFlash('danger', $t->trans('Password can not be reset. Log in with facebook account instead.'));
            return $this->render('Frontend/Guest/reset_password.html.twig');
        }
        $form = $this->createFormBuilder([])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'New password'],
                'second_options' => ['label' => 'Confirm new password'],
                'constraints' => [new NotBlank()]
            ])
            ->add('Change', SubmitType::class, array('label' => 'Change'))
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->getData()['newPassword'];
            $user->resetPassword($encoder->encodePassword($user, $password));
            $log = $user->createPasswordChangeLog($this->dumpRequest($request));
            RegistryHelper::store([$log, $user], $registry);

            $this->addFlash(
                'success',
                $t->trans('Your password was successfully changed.')
            );
            return $this->redirectToRoute('guest_login');
        }

        return $this->render(
            'Frontend/Guest/reset_password.html.twig',
            [ 'form' => $form->createView(), ]
        );
    }


    /**
     * Allows user to reset account password. This action generates reset token that is send to user's
     * email. Password is not reset directly.
     *
     * @Route(
     *     name="guest_request_password_reset",
     *     path="/guest/request-password-reset",
     * )
     *
     * @param Request $request
     * @param ManagerRegistry $registry
     * @param MessengerInterface $messenger
     * @param TranslatorInterface $t
     * @return Response
     */
    public function requestPasswordReset(
        Request $request,
        ManagerRegistry $registry,
        MessengerInterface $messenger,
        TranslatorInterface $t): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('Reset', SubmitType::class, ['label' => 'Reset'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /** @var Guest $user */
            $user = $registry->getManager()
                ->getRepository(Guest::class)
                ->findOneBy(['email' => $data['email']]);

            if ($user && !$user->isVerified()) {
                $form->addError(new FormError($t->trans('Please confirm email provided during registration at first.')));
            } else {
                if ($user && $user->isEmailLoginAllowed()) {
                    RegistryHelper::store([$user->regenerateToken()], $registry);

                    $message = (new Message())
                        ->setType(Message::TYPE_GUEST_RESET_PASSWORD)
                        ->setSubject($t->trans('Password reset requested'))
                        ->setTo($data['email'])
                        ->setParams([
                            'url' => $this->generateUrl('guest_reset_password', ['token'=> $user->getToken()]),
                        ]);
                    $messenger->send($message);
                } elseif ($user) {
                    $this->addFlash('warning', $t->trans('Your account was registered via Facebook. Please login via FB.'));
                    return $this->redirectToRoute('guest_request_password_reset');
                }

                $this->addFlash(
                    'notice',
                    sprintf(
                        $t->trans('We\'ll send you instructions on how to reset your password or create an account, if no one is associated with "%s". If you do not receive an email from us, please check your spam folder.'),
                        $user->getEmail()
                    )
                );
                return $this->redirectToRoute('guest_completed');
            }
        }

        return $this->render(
            'Frontend/Guest/change_password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * Confirms email provided by user
     *
     * @Route(
     *     name="guest_verify_account",
     *     path="/guest/verify-account/{token}",
     * )
     *
     * @param Request $request
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $t
     * @return Response
     */
    public function verifyAccount(Request $request, ManagerRegistry $registry, TranslatorInterface $t): Response
    {
        $token = $request->attributes->get('token');
        $success = false;

        if (!$token) {
            $message = $t->trans('Url is not complete.');
        } else {
            /** @var Guest $user */
            $user = $registry->getManager()
                ->getRepository(Guest::class)
                ->findOneBy(['token' => $token]);

            if ($user && $user->isTokenValid()) {
                $user->verifyEmail();
                $log = $user->createAccountVerificationLog($this->dumpRequest($request));
                RegistryHelper::store([$log, $user], $registry);
                $message = $t->trans('Email is confirmed');
                $success = true;
            } else {
                $message = $t->trans('Url is not valid. Please generate new confirmation token on profile page');
            }
        }

        $this->addFlash(
            $success ? 'success' : 'warning',
            $message
            );

        return $this->redirectToRoute('guest_profile');
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createRegistrationForm()
    {
        return $this->createFormBuilder(new Guest())
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('agreeWithTerms', CheckboxType::class, ['mapped' => false])
            ->add('save', SubmitType::class, ['label' => 'Register'])
            ->getForm();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function dumpRequest(Request $request): array
    {
        return RequestDumper::dump($request, [RequestDumper::URL, RequestDumper::USER_INFO]);
    }

    /**
     * @param Request $request
     * @param RegistrationHelper $registrationHelper
     * @param Guest $user
     * @param UserPasswordEncoderInterface $encoder
     * @param FormFactoryInterface $formFactory
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    private function handleAccountPairing(
        Request $request,
        RegistrationHelper $registrationHelper,
        Guest $user,
        UserPasswordEncoderInterface $encoder,
        FormFactoryInterface $formFactory,
        ManagerRegistry $registry,
        TranslatorInterface $t
    ) {
        $fbInfo = $registrationHelper->getPendingOauthUserData();
        $form = $formFactory
            ->createNamed(null)
            ->add('password', PasswordType::class)
            ->add('pair', CheckboxType::class)
            ->add('Login', SubmitType::class, array('label' => 'Login'));

        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            $form->addError(new FormError(
                sprintf(
                $t->trans('There is already registered account with email %s. Yoo can use your password to login. If you want to use both login methods, you can pair your facebook account.'),
                $fbInfo['email']
            )
            ));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$encoder->isPasswordValid($user, $form->getData()['password'])) {
                $form->addError(new FormError($t->trans('Current password is not valid. You can request password recovery from login page.')));
                RegistryHelper::store([
                    $user->createLoginFailureLog($this->dumpRequest($request))
                ], $registry);
            } else {
                if ($form->getData()['pair']) {
                    $user->setFacebookId($fbInfo['facebookId']);
                    $user->setFacebookPaired(true);
                    $log = $user->createRegistrationLog($this->dumpRequest($request));
                    RegistryHelper::store([$log, $user], $registry);

                    $registrationHelper->authenticateOauthAccount($user);

                    $this->addFlash(
                        'success',
                        $t->trans('Your account was paired.')
                    );
                } else {
                    $log = $user->createLoginSuccessLog($this->dumpRequest($request));
                    RegistryHelper::store([$log], $registry);
                    $registrationHelper->authenticateEmailAccount($user);
                }
                return $this->redirectToRoute('guest_profile');
            }
        }

        return $this->render(
            'Frontend/Guest/register.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param Request $request
     * @param RegistrationHelper $registrationHelper
     * @param FormFactoryInterface $formFactory
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    private function handleFbAccountRegistration(
        Request $request,
        RegistrationHelper $registrationHelper,
        FormFactoryInterface $formFactory,
        ManagerRegistry $registry,
        TranslatorInterface $t
    ) {
        $fbInfo = $registrationHelper->getPendingOauthUserData();
        $form = $formFactory
            ->createNamed(null)
            ->add('agreeWithTerms', CheckboxType::class)
            ->add('Register', SubmitType::class, array('label' => 'Register'));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = Guest::createFromFbData($fbInfo['facebookId'], $fbInfo['email'], $fbInfo['name']);
            $log = $user->createRegistrationLog($this->dumpRequest($request));
            RegistryHelper::store([$log, $user], $registry);

            $registrationHelper->authenticateOauthAccount($user);

            $this->addFlash(
                'success',
                $t->trans('Thank you for creating new account.')
            );
            return $this->redirectToRoute('guest_profile');
        }

        return $this->render(
            'Frontend/Guest/register.html.twig',
            ['form' => $form->createView()]
        );
    }
}
