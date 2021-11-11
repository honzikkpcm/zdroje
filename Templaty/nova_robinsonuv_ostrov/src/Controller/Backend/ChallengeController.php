<?php

namespace App\Controller\Backend;

use App\Entity\Challenge;
use App\Entity\ChallengeTask;
use App\Form\DataTransformer\DateTimeDataTransformer;
use App\Form\DataTransformer\FileDataTransformer;
use App\Form\DataTransformer\UrlcodeDataTransformer;
use App\Service\ImageStorageInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ChallengeController
 * @package App\Controller\Backend
 */
class ChallengeController extends BackendController
{
    /** @var DateTimeDataTransformer */
    private $dateTimeTransformer;

    /** @var UrlcodeDataTransformer */
    private $urlcodeTransdormer;

    /** @var FileDataTransformer */
    private $fileTransformer;

    /** @var ImageStorageInterface */
    private $imageStorage;

    /**
     * @param DateTimeDataTransformer $dateTimeTransformer
     * @param UrlcodeDataTransformer $urlcodeTransformer
     * @param FileDataTransformer $fileTransformer
     * @param ImageStorageInterface $imageStorage
     */
    public function __construct(
        DateTimeDataTransformer $dateTimeTransformer,
        UrlcodeDataTransformer $urlcodeTransformer,
        FileDataTransformer $fileTransformer,
        ImageStorageInterface $imageStorage)
    {
        $this->dateTimeTransformer = $dateTimeTransformer;
        $this->urlcodeTransdormer = $urlcodeTransformer;
        $this->fileTransformer = $fileTransformer;
        $this->imageStorage = $imageStorage;
    }

    /**
     * @Route("/challenge", name="challenge")
     */
    public function index(): Response
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Challenges',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'number' => [
                        'caption' => 'No.',
                    ],
                    'name' => [
                        'caption' => 'Name',
                    ],
                    'valid_from' => [
                        'caption' => 'Valid from',
                        'type' => 'datetime',
                    ],
                    'valid_to' => [
                        'caption' => 'Valid to',
                        'type' => 'datetime',
                    ],
                    'active' => [
                        'caption' => 'Status',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_STATUS,
                    ],
                    '_actions' => [
                        'actions' => [
                            'add' => $this->generateUrl('challenge-add'),
                            'edit' => $this->generateUrl('challenge-edit', ['id' => '--id--']),
                            'tasks' => $this->generateUrl('challenge-tasks', ['id' => '--id--']),
                            'delete' => $this->generateUrl('challenge-delete', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [
                    \App\Twig\JsonGridExtension::SETTING_ADDITIONAL_ACTIONS => [
                        'tasks' => [
                            'key' => true,
                            'icon' => 'list-ul',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @Route("/challenge/add", name="challenge-add")
     * @param Request $request
     * @return Response
     */
    public function add(Request $request): Response
    {
        $challenge = new Challenge();
        $form = $this->getForm($challenge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($challenge);
            $em->flush();

            $this->addFlash('success', 'The item has been added.');
            return $this->json([
                'redirect' => $this->generateUrl('challenge'),
            ]);
        }

        return $this->render('Backend/view/modal.html.twig', [
            'form' => $form->createView(),
            'h1' => 'Add challenge',
        ]);
    }

    /**
     * @Route("/challenge/edit/{id}", name="challenge-edit")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function edit(Request $request, int $id)
    {
        /** @var \App\Entity\Challenge $challenge */
        $challenge = $this->getDoctrine()
            ->getRepository(Challenge::class)
            ->find($id);

        if (empty($challenge)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        $form = $this->getForm($challenge, $this->generateUrl('challenge-edit', ['id' => $id]));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($challenge);
            $em->flush();

            $this->addFlash('success', 'The item has been updated.');
            return $this->json([
                'redirect' => $this->generateUrl('challenge'),
            ]);
        }

        return $this->render('Backend/view/modal.html.twig', [
            'form' => $form->createView(),
            'h1' => 'Edit challenge',
        ]);
    }

    /**
     * @Route("/challenge/tasks/{id}", name="challenge-tasks")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function tasks(Request $request, int $id)
    {
        /** @var Challenge $challenge */
        $challenge = $this->getDoctrine()
            ->getRepository(Challenge::class)
            ->find($id);

        if (empty($challenge)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        return $this->render('Backend/view/challenge-tasks.html.twig', [
            'id' => $id,
            'title' => 'Challenge tasks' .' / ' . $challenge->getName(),
            'grid' => [
                'data' => $this->getTasksGridData($challenge),
                'columns' => [
                    'type' => [
                        'caption' => 'Type',
                        'replacement' => [
                            ChallengeTask::TYPE_QUIZ_ABCD => 'ABCD quiz',
                            ChallengeTask::TYPE_QUIZ_PHOTO => 'Photos quiz',
                            ChallengeTask::TYPE_ORDER_PHOTOS => 'Order photos',
                            ChallengeTask::TYPE_SELECT_PHOTO => 'Select photo',
                            ChallengeTask::TYPE_WORD => 'Word',
                            ChallengeTask::TYPE_PHOTO => 'Photo',
                            ChallengeTask::TYPE_VIDEO => 'Video',
                            ChallengeTask::TYPE_GUESS => 'Guess',
                        ],
                    ],
                    'caption' => [
                        'caption' => 'Caption',
                    ],
                    'score' => [
                        'caption' => 'Score',
                    ],
                    'active' => [
                        'caption' => 'Status',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_STATUS,
                    ],
                    '_actions' => [
                        'actions' => [
                            'edit' => $this->generateUrl('challenge-task-edit', ['id' => '--id--']),
                            'delete' => $this->generateUrl('challenge-task-delete', ['id' => '--id--']),
                            'sort-up' => $this->generateUrl('challenge-task-sort-up', ['id' => '--id--']),
                            'sort-down' => $this->generateUrl('challenge-task-sort-down', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [
                    \App\Twig\JsonGridExtension::SETTING_DISABLE_ORDERING => true,
                ],
            ],
        ]);
    }

    /**
     * @Route("/challenge/delete/{id}", name="challenge-delete")
     * @param int $id
     * @param LoggerInterface $logger
     * @return Response
     */
    public function delete(int $id, LoggerInterface $logger): Response
    {
        $challenge = $this->getDoctrine()
            ->getRepository(Challenge::class)
            ->find($id);

        if (empty($challenge)) {
            $this->addFlash('warning', "Can not find item #$id.");
            return $this->redirectToRoute('challenge');
        }

        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($challenge);
            $em->flush();

            $this->addFlash('success', 'The item has been deleted.');
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', "Can not delete item #$id.");
        }

        return $this->redirectToRoute('challenge');
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @param Challenge $challenge
     * @param string $action
     * @return FormInterface
     */
    private function getForm(Challenge $challenge, string $action = null): FormInterface
    {
        $builder = $this->createFormBuilder($challenge)
            ->add('name', TextType::class)
            ->add('urlcode', TextType::class, [
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'standard' => Challenge::TYPE_STANDARD,
                    'bonus' => Challenge::TYPE_BONUS,
                ],
            ])
            ->add('bonus_answers', TextType::class, [
                'required' => false,
                'label' => 'Bonus answers (comma separated)',
            ])
            ->add('bonus_max_time', IntegerType::class, [
                'required' => false,
                'label' => 'Bonus max time (only for bonus time challenge, time when guest has 0 points for a quiz)',
            ])
            ->add('description', TextareaType::class)
            ->add('valid_from', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
            ])
            ->add('valid_to', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
            ])
            ->add('number', IntegerType::class)
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('active', ChoiceType::class, [
                'choices' => [
                    'not active' => 0,
                    'active' => 1,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->setAction(isset($action) ? $action : $this->generateUrl('challenge-add'))
            ->addEventSubscriber(new \App\Form\EventListener\FileUploadEventListener($this->imageStorage));

        $builder->get('urlcode')->addModelTransformer($this->urlcodeTransdormer);
        $builder->get('valid_from')->addViewTransformer($this->dateTimeTransformer);
        $builder->get('valid_to')->addViewTransformer($this->dateTimeTransformer);
        $builder->get('image')->addViewTransformer($this->fileTransformer);

        return $builder->getForm();
    }

    /**
     * @return array
     */
    private function getGridData(): array
    {
        /** @var Challenge[] $challenges */
        $challenges = $this->getDoctrine()
            ->getRepository(Challenge::class)
            ->findAll();

        if (empty($challenges)) {
            return [];
        }

        $data = [];

        foreach ($challenges as $item) {
            $data[] = [
                'id' => $item->getId(),
                'number' => $item->getNumber(),
                'name' => $item->getName(),
                'valid_from' => $item->getValidFrom(),
                'valid_to' => $item->getValidTo(),
                'active' => $item->isActive(),
            ];
        }

        return $data;
    }

    /**
     * @param Challenge $challenge
     * @return array
     */
    private function getTasksGridData(Challenge $challenge): array
    {
        /** @var ChallengeTask[] $challengeTasks */
        $challengeTasks = $this->getDoctrine()
            ->getRepository(ChallengeTask::class)
            ->findBy([
                'challenge' => $challenge,
            ], [
                'sorting' => 'ASC',
            ]);

        if (empty($challengeTasks)) {
            return [];
        }

        $data = [];

        foreach ($challengeTasks as $item) {
            $data[] = [
                'id' => $item->getId(),
                'type' => $item->getType(),
                'caption' => $item->getCaption(),
                'score' => $item->getScore(),
                'active' => $item->isActive(),
            ];
        }

        return $data;
    }
}
