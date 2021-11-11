<?php

namespace App\Controller\Backend;
use App\Entity\Message;
use App\Entity\Staff;
use App\Entity\StaffEventLog;
use App\Service\MessengerInterface;
use App\Utils\RegistryHelper;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use App\Utils\RequestDumper;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class StaffController
 * @package App\Controller\Backend
 */
class StaffController extends BackendController
{

    /**
     * Shows login form.
     *
     * @Route(
     *     name="staff_login",
     *     path="staff/login",
     * )
     *
     * @param Request $request
     * @param AuthenticationUtils $authHelper
     * @param FormFactoryInterface $formFactory
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function loginAction(
        Request $request,
        AuthenticationUtils $authHelper,
        FormFactoryInterface $formFactory
    ) {
        $form = $formFactory
            ->createNamed(null, FormType::class, [
                'email' => $authHelper->getLastUsername(),
            ], [
                'action' => $this->generateUrl('staff_login_check')
            ])
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('Login', SubmitType::class, array('label' => 'Login'));

        return $this->render(
            'Backend/view/auth-login.html.twig',
            [
                'error' => $authHelper->getLastAuthenticationError(),
                'form' => $form->createView(),
                'passwordResetForm' => $this->getResetRequestForm()->createView(),
            ]
        );
    }

    /**
     * Allows user to change account password.
     * User must be logged in.
     * Old user password must be provided.
     * New password must be different then old one.
     *
     * @Route(
     *     name="staff_change_password",
     *     path="/staff/change-password",
     * )
     *
     * @param Request $request
     * @param UserInterface $user
     * @param UserPasswordEncoderInterface $encoder
     * @param ManagerRegistry $registry
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePasswordAction(
        Request $request,
        UserInterface $user,
        UserPasswordEncoderInterface $encoder,
        ManagerRegistry $registry,
        TranslatorInterface $t
    ) {
        /** @var Staff $user */
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
            ->add('Change', SubmitType::class, array('label' => 'Change'))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['newPassword'] == $data['currentPassword']) {
                $form->addError(new FormError($t->trans('New password can not be same as old password')));
            } elseif (!$encoder->isPasswordValid($user, $data['currentPassword'])) {
                $form->addError(new FormError($t->trans('Current password is not valid.')));
                $log = $user->createLoginFailureLog(
                    RequestDumper::dump($request, [RequestDumper::URL, RequestDumper::USER_INFO])
                );
                RegistryHelper::store([$log], $registry);
            } else {
                $user->changePassword($encoder->encodePassword($user, $data['newPassword']), false);
                $log =  $user->createPasswordChangeLog(RequestDumper::dump($request, [
                    RequestDumper::URL,
                    RequestDumper::USER_INFO,
                ]));
                RegistryHelper::store([$user, $log], $registry);
                $this->addFlash(
                    'success',
                    $t->trans('Your password was successfully changed.')
                );
                return $this->redirectToRoute('dashboard');
            }
        }

        return $this->render(
            'Backend/view/auth-password-change.html.twig',
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
     *     name="staff_reset_password",
     *     path="/staff/reset-password/{token}",
     * )
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPasswordAction(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        ManagerRegistry $registry,
        TranslatorInterface $t
    ) {
        $token = $request->attributes->get('token');
        /** @var Staff $user */
        $user = $registry
            ->getManager()
            ->getRepository(Staff::class)
            ->findOneBy(['token' => $token]);
        if (!$user || !$user->isTokenValid()) {
            $log = StaffEventLog::createInvalidTokenUsageLog(RequestDumper::dump($request, [
                RequestDumper::URL,
                RequestDumper::USER_INFO
            ]));
            RegistryHelper::store([$log], $registry);
            return $this->render(
                'Backend/view/auth-password-reset.html.twig',
                [ 'error' => $t->trans('Url is not valid. Please generate new reset token') ]
            );
        }
        $form = $this->createFormBuilder([])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'New password'],
                'second_options' => ['label' => 'Confirm new password'],
                'constraints' => [new NotBlank()]
            ])
            ->add('Change', SubmitType::class, array('label' => 'Change password'))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->getData()['newPassword'];
            $user->changePassword($encoder->encodePassword($user, $password), true);
            $log =  $user->createPasswordChangeLog(RequestDumper::dump($request, [
                RequestDumper::URL,
                RequestDumper::USER_INFO,
            ]));
            RegistryHelper::store([$user, $log], $registry);

            $this->addFlash(
                'success',
                $t->trans('Your password was successfully changed.')
            );
            return $this->redirectToRoute('staff_login');
        }

        return $this->render(
            'Backend/view/auth-password-reset.html.twig',
            [ 'form' => $form->createView(), ]
        );
    }


    /**
     * Allows user to reset account password. This action generates reset token that is send to user's
     * email. Password is not reset directly.
     *
     * @Route(
     *     name="staff_request_password_reset",
     *     path="/staff/request-password-reset",
     *     methods="POST"
     * )
     *
     * @param Request $request
     * @param ManagerRegistry $registry
     * @param MessengerInterface $messenger
     * @param TranslatorInterface $t
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestPasswordResetAction(
        Request $request,
        ManagerRegistry $registry,
        MessengerInterface $messenger,
        TranslatorInterface $t
    ) {
        $form = $this->getResetRequestForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new Response('', 400);
        }

        if ($form->isValid()) {
            $data = $form->getData();
            /** @var Staff $user */
            $user = $registry->getManager()
                ->getRepository(Staff::class)
                ->findOneBy(['email' => $data['email']]);

            if ($user) {
                RegistryHelper::store([$user->regenerateToken()], $registry);

                $message = (new Message())
                    ->setType(Message::TYPE_STAFF_RESET_PASSWORD)
                    ->setSubject($t->trans('Password reset requested'))
                    ->setTo($data['email'])
                    ->setParams([
                        'url' => $this->generateUrl('staff_reset_password', ['token'=> $user->getToken()]),
                    ]);
                $messenger->send($message);
            } else {
                $requestData = RequestDumper::dump($request, [RequestDumper::URL, RequestDumper::USER_INFO]);
                $log = StaffEventLog::createInvalidResetEmailLog($requestData, $data['email']);
                RegistryHelper::store([$log], $registry);
            }

            $this->addFlash(
                'notice',
                sprintf(
                    $t->trans('We\'ll send you instructions on how to reset your password or create an account, if no one is associated with "%s". If you do not receive an email from us, please check your spam folder.'),
                    $user->getEmail()
                )
            );
        } else {
            $this->addFlash(
                'error',
                (string) $form->getErrors(true)
            );
        }
        return $this->redirectToRoute('staff_login');
    }

    /**
     * @Route("/staff", name="staff")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Staff',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'name' => [
                        'caption' => 'Name',
                    ],
                    'email' => [
                        'caption' => 'Email',
                    ],
                    'comment' => [
                        'caption' => 'Comment',
                    ],
                    'role' => [
                        'caption' => 'Role',
                    ],
                    'active' => [
                        'caption' => 'Status',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_STATUS,
                    ],
                    '_actions' => [
                        'actions' => [
                            'add' => $this->generateUrl('staff-add'),
                            'edit' => $this->generateUrl('staff-edit', ['id' => '--id--']),
                            'delete' => $this->generateUrl('staff-delete', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [],
            ],
        ]);
    }

    /**
     * @Route("/staff/add", name="staff-add")
     * @param Request $request
     * @param MessengerInterface $messenger
     * @param TranslatorInterface $t
     * @return Response
     */
    public function add(Request $request, MessengerInterface $messenger, TranslatorInterface $t): Response
    {
        $staff = new Staff();
        $form = $this->getForm($staff);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // user will receive information how to reset password
            // it would be security risk to send password
            $staff->setPassword('will not be used');
            $staff->regenerateToken();

            RegistryHelper::store([$staff], $this->getDoctrine());

            $message = (new Message())
                ->setType(Message::TYPE_STAFF_REGISTRATION)
                ->setTo($staff->getEmail())
                ->setSubject($t->trans('New registration'))
                ->setParams([
                    'url' => $this->generateUrl('staff_reset_password', ['token'=> $staff->getToken()]),
                    'reset_url' => $this->generateUrl('staff_login', ['_fragment' => 'reset-wrapper']),
                    'login_url' => $this->generateUrl('staff_login'),
                    'username' => $staff->getUsername(),
                ]);
            $messenger->send($message);

            $this->addFlash('success', 'The item has been added.');
            return $this->json([
                'redirect' => $this->generateUrl('staff'),
            ]);
        }

        return $this->render('Backend/view/modal.html.twig', [
            'form' => $form->createView(),
            'h1' => 'Add staff',
        ]);
    }

    /**
     * @Route("/staff/edit/{id}", name="staff-edit")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function edit(Request $request, int $id)
    {
        /** @var \App\Entity\Staff $staff */
        $staff = $this->getDoctrine()
            ->getRepository(Staff::class)
            ->find($id);

        if (empty($staff)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        $form = $this->getForm($staff, $this->generateUrl('staff-edit', ['id' => $id]));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($staff);
            $em->flush();

            $this->addFlash('success', 'The item has been updated.');
            return $this->json([
                'redirect' => $this->generateUrl('staff'),
            ]);
        }

        return $this->render('Backend/view/modal.html.twig', [
            'form' => $form->createView(),
            'h1' => 'Edit staff',
        ]);
    }

    /**
     * @Route("/staff/delete/{id}", name="staff-delete")
     * @param int $id
     * @param LoggerInterface $logger
     * @return Response
     */
    public function delete(int $id, LoggerInterface $logger): Response
    {
        $staff = $this->getDoctrine()
            ->getRepository(Staff::class)
            ->find($id);

        if (empty($staff)) {
            $this->addFlash('warning', "Can not find item #$id.");
            return $this->redirectToRoute('staff');
        }

        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($staff);
            $em->flush();

            $this->addFlash('success', 'The item has been deleted.');
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', "Can not delete item #$id.");
        }

        return $this->redirectToRoute('staff');
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @param Staff $staff
     * @param string $action
     * @return FormInterface
     */
    private function getForm(Staff $staff, string $action = null): FormInterface
    {
        $builder = $this->createFormBuilder($staff)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('comment', TextareaType::class)
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'administrator' => Staff::ROLE_ADMIN,
                    'editor' => Staff::ROLE_EDITOR,
                ],
            ])
            ->add('active', ChoiceType::class, [
                'choices' => [
                    'not active' => false,
                    'active' => true,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->setAction(isset($action) ? $action : $this->generateUrl('staff-add'));

        return $builder->getForm();
    }

    /**
     * @return array
     */
    private function getGridData(): array
    {
        /** @var Staff[] $staffs */
        $staffs = $this->getDoctrine()
            ->getRepository(Staff::class)
            ->findAll();

        if (empty($staffs)) {
            return [];
        }

        $data = [];

        foreach ($staffs as $item) {
            $data[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'email' => $item->getEmail(),
                'comment' => $item->getComment(),
                'role' => $item->getRole(),
                'active' => $item->isActive(),
            ];
        }

        return $data;
    }

    /**
     * @return FormInterface
     */
    private function getResetRequestForm(): FormInterface
    {
        return $this->createForm(FormType::class, [], [
                'action' => $this->generateUrl('staff_request_password_reset')
            ])
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('Reset', SubmitType::class, ['label' => 'Request password reset']);
    }
}
