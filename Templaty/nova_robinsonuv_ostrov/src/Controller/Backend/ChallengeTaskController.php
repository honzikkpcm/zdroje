<?php

namespace App\Controller\Backend;

use App\Entity\Challenge;
use App\Entity\ChallengeTask;
use App\Repository\EntitySorting;
use App\Service\Cloudinary;
use App\Service\ImageStorageInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ChallengeTaskController
 * @package App\Controller\Backend
 */
class ChallengeTaskController extends BackendController
{
    /** @var string */
    private $view = 'challenge-task.modal';

    /** @var string */
    private $type;

    /** @var array */
    private $processItems = [
        ChallengeTask::TYPE_QUIZ_ABCD => [
            ChallengeTask::DATA_ANSWERS,
        ],
        ChallengeTask::TYPE_QUIZ_PHOTO => [
            ChallengeTask::DATA_ANSWERS,
        ],
        ChallengeTask::TYPE_ORDER_PHOTOS => [
            ChallengeTask::DATA_SORTABLE,
        ],
        ChallengeTask::TYPE_SELECT_PHOTO => [
            ChallengeTask::DATA_IMAGE,
            ChallengeTask::DATA_ANSWERS,
        ],
        ChallengeTask::TYPE_WORD => [
            ChallengeTask::DATA_IMAGE,
            ChallengeTask::DATA_ANSWER,
        ],
        ChallengeTask::TYPE_PHOTO => [
            ChallengeTask::DATA_IMAGE,
        ],
        ChallengeTask::TYPE_VIDEO => [
            ChallengeTask::DATA_VIDEO,
        ],
        ChallengeTask::TYPE_GUESS => [
            ChallengeTask::DATA_ANSWERS,
        ],
    ];

    /** @var string */
    private $uploadImageSize = '6M';

    /** @var array */
    private $uploadImageMimeTypes = [
        'image/jpeg',
    ];

    /** @var string */
    private $uploadVideoSize = '32M';

    /** @var array */
    private $uploadVideoMimeTypes = [
        'video/mpeg',
    ];

    /** @var string */
    private $imageInternalSize = (1 * 1024 * 1024); // 1MB

    /** @var array */
    private $imageInternalMimeTypes = [
        'image/jpeg',
        'image/png',
    ];

    /** @var ImageStorageInterface */
    private $imageStorage;

    /** @var array */
    private $miniature = [
        'width' => 310,
        'height' => 210,
    ];

    /**
     * @param ImageStorageInterface $imageStorage
     */
    public function __construct(ImageStorageInterface $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    /**
     * @Route("/challenge-task/add/{type}/{challengeId}", name="challenge-task-add")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EntitySorting $sorting
     * @param int $challengeId
     * @param string $type
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function add(
        Request $request,
        TranslatorInterface $translator,
        EntitySorting $sorting,
        int $challengeId,
        string $type): Response
    {
        /** @var Challenge $challenge */
        $challenge = $this->getDoctrine()
            ->getRepository(Challenge::class)
            ->find($challengeId);

        if (empty($challenge)) {
            $this->addFlash('warning', "Can not find item #$challengeId.");
            return $this->redirectToRoute('challenge');
        }

        $this->type = $type;

        /** @var FormInterface $form */
        $form = $this->getForm([], [
            'actionUrl' => $this->generateUrl('challenge-task-add', [
                'type' => $type,
                'challengeId' => $challengeId,
            ]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $challengeTask = new ChallengeTask();
            $challengeTask->setType($type);
            $challengeTask->setChallenge($challenge);
            $challengeTask->setSorting($sorting->getLastSorting(ChallengeTask::class) + 1);
            $this->processForm($form, $challengeTask);

            $em = $this->getDoctrine()->getManager();
            $em->persist($challengeTask);
            $em->flush();

            $this->addFlash('success', $translator->trans('The item has been added.'));

            return $this->json([
                'redirect' => $this->generateUrl('challenge-tasks', [
                    'id' => $challengeId,
                ]),
            ]);
        }

        return $this->render("Backend/view/$this->view.html.twig", [
            'form' => $form->createView(),
            'type' => $type,
            'h1' => 'Add challenge task',
            'imageInternal' => [
                'type' => $this->imageInternalMimeTypes,
                'size' => $this->imageInternalSize,
            ],
            'setting' => [
                'hasImage' => in_array(ChallengeTask::DATA_IMAGE, $this->processItems[$type]),
                'hasVideo' => in_array(ChallengeTask::DATA_VIDEO, $this->processItems[$type]),
                'hasAnswer' => in_array(ChallengeTask::DATA_ANSWER, $this->processItems[$type]),
                'hasAnswers' => in_array(ChallengeTask::DATA_ANSWERS, $this->processItems[$type]),
                'hasSortable' => in_array(ChallengeTask::DATA_SORTABLE, $this->processItems[$type]),
                'numberOfRightAnswers' => 2,
            ],
        ]);
    }

    /**
     * @Route("/challenge-task/edit/{id}", name="challenge-task-edit")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function edit(Request $request, TranslatorInterface $translator, int $id)
    {
        /** @var ChallengeTask $challengeTask */
        $challengeTask = $this->getDoctrine()
            ->getRepository(ChallengeTask::class)
            ->find($id);

        if (empty($challengeTask)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        $type = $this->type = $challengeTask->getType();

        /** @var FormInterface $form */
        $form = $this->getForm($this->getFormData($challengeTask), [
            'actionUrl' => $this->generateUrl('challenge-task-edit', [
                'id' => $id,
            ]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processForm($form, $challengeTask);

            $em = $this->getDoctrine()->getManager();
            $em->persist($challengeTask);
            $em->flush();

            $this->addFlash('success', $translator->trans('The item has been updated.'));

            return $this->json([
                'redirect' => $this->generateUrl('challenge-tasks', [
                    'id' => $challengeTask->getChallenge()->getId(),
                ]),
            ]);
        }

        return $this->render("Backend/view/$this->view.html.twig", [
            'form' => $form->createView(),
            'h1' => 'Edit challenge task',
            'type' => $challengeTask->getType(),
            'imageInternal' => [
                'type' => $this->imageInternalMimeTypes,
                'size' => $this->imageInternalSize,
            ],
            'setting' => [
                'hasImage' => in_array(ChallengeTask::DATA_IMAGE, $this->processItems[$type]),
                'hasVideo' => in_array(ChallengeTask::DATA_VIDEO, $this->processItems[$type]),
                'hasAnswer' => in_array(ChallengeTask::DATA_ANSWER, $this->processItems[$type]),
                'hasAnswers' => in_array(ChallengeTask::DATA_ANSWERS, $this->processItems[$type]),
                'hasSortable' => in_array(ChallengeTask::DATA_SORTABLE, $this->processItems[$type]),
                'numberOfRightAnswers' => 2,
            ],
        ]);
    }

    /**
     * @Route("/challenge-task/delete/{id}", name="challenge-task-delete")
     * @param int $id
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete(int $id, LoggerInterface $logger, TranslatorInterface $translator): Response
    {
        /** @var ChallengeTask $challengeTask */
        $challengeTask = $this->getDoctrine()
            ->getRepository(ChallengeTask::class)
            ->find($id);

        if (empty($challengeTask)) {
            $this->addFlash('warning', "Can not find item #$id.");
            return $this->redirectToRoute('challenge');
        }

        $challengeId = $challengeTask->getChallenge()->getId();

        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($challengeTask);
            $em->flush();

            $this->addFlash('success', $translator->trans('The item has been deleted.'));
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', "Can not delete item #$id.");
        }

        return $this->redirectToRoute('challenge-tasks', [
            'id' => $challengeId,
        ]);
    }

    /**
     * @Route("/challenge-task/sort-up/{id}", name="challenge-task-sort-up")
     * @param int $id
     * @param EntitySorting $sorting
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function sortUp(int $id, EntitySorting $sorting, LoggerInterface $logger, TranslatorInterface $translator): Response
    {
        /** @var ChallengeTask $challengeTask */
        $challengeTask = $this->getDoctrine()
            ->getRepository(ChallengeTask::class)
            ->find($id);

        if (empty($challengeTask)) {
            $this->addFlash('warning', "Can not find item #$id.");
            return $this->redirectToRoute('challenge');
        }

        $challengeId = $challengeTask->getChallenge()->getId();

        try {
            $sorting->sortingUp(ChallengeTask::class, $id, 'challenge_id');
            $this->addFlash('success', $translator->trans('The item has been deleted.'));
        } catch (\UnexpectedValueException $e) {
            $this->addFlash('warning', $e->getMessage());
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', "Can not sort item #$id.");
        }

        return $this->redirectToRoute('challenge-tasks', [
            'id' => $challengeId,
        ]);
    }

    /**
     * @Route("/challenge-task/sort-down/{id}", name="challenge-task-sort-down")
     * @param int $id
     * @param EntitySorting $sorting
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function sortDown(int $id, EntitySorting $sorting, LoggerInterface $logger, TranslatorInterface $translator): Response
    {
        /** @var ChallengeTask $challengeTask */
        $challengeTask = $this->getDoctrine()
            ->getRepository(ChallengeTask::class)
            ->find($id);

        if (empty($challengeTask)) {
            $this->addFlash('warning', "Can not find item #$id.");
            return $this->redirectToRoute('challenge');
        }

        $challengeId = $challengeTask->getChallenge()->getId();

        try {
            $sorting->sortingDown(ChallengeTask::class, $id, 'challenge_id');
            $this->addFlash('success', $translator->trans('The item has been deleted.'));
        } catch (\UnexpectedValueException $e) {
            $this->addFlash('warning', $e->getMessage());
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', "Can not sort item #$id.");
        }

        return $this->redirectToRoute('challenge-tasks', [
            'id' => $challengeId,
        ]);
    }


    // process method --------------------------------------------------------------------------------------------------

    /**
     * @param array $default
     * @param array $params
     * @return FormInterface
     */
    public function getForm(array $default = [], array $params = []): FormInterface
    {
        return $this->createFormBuilder($default)
            ->add('caption', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 512,
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('score', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0]),
                ],
            ])
            ->add('active', ChoiceType::class, [
                'choices' => [
                    'not active' => 0,
                    'active' => 1,
                ],
            ])
            ->add('data', HiddenType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->setAction($params['actionUrl'])
            ->getForm();
    }

    /**
     * @param ChallengeTask $challengeTask
     * @return array
     */
    public function getFormData(ChallengeTask $challengeTask): array
    {
        $data = $challengeTask->getData();
        array_walk_recursive($data, function(&$item, $key) {
            if ($key == ChallengeTask::DATA_IMAGE) {
                $item = $this->imageStorage->resize($item, $this->miniature['width'], $this->miniature['height'], [
                    'format' => 'jpg',
                    'crop' => 'fill',
                ]);
            }
        });

        return [
            'caption' => $challengeTask->getCaption(),
            'description' => $challengeTask->getDescription(),
            'score' => $challengeTask->getScore(),
            'active' => $challengeTask->isActive(),
            'data' => json_encode($data),
        ];
    }

    /**
     * @param FormInterface $form
     * @param  ChallengeTask $challengeTask
     */
    private function processForm(FormInterface $form, ChallengeTask $challengeTask): void
    {
        $formData = $form->getData();
        $formDataData = json_decode($formData['data'], true);

        $challengeTask->setCaption($formData['caption']);
        $challengeTask->setDescription($formData['description'] ?? '');
        $challengeTask->setScore($formData['score']);
        $challengeTask->setActive($formData['active']);

        foreach ($this->processItems[$this->type] as $item) {
            call_user_func([$this, 'processForm' . ucfirst($item)], $challengeTask, $formDataData);
        }
    }

    /**
     * @param ChallengeTask $challengeTask
     * @param array $formDataData
     */
    private function processFormImage(ChallengeTask $challengeTask, array $formDataData): void
    {
        $ctData = $challengeTask->getData();

        $ctData[ChallengeTask::DATA_IMAGE] = null;

        if (isset($formDataData[ChallengeTask::DATA_IMAGE])) {
            if ($formDataData[ChallengeTask::DATA_IMAGE]['type'] == 'url') {
                $ctData[ChallengeTask::DATA_IMAGE] = Cloudinary::parseId($formDataData[ChallengeTask::DATA_IMAGE]['src']);
            } elseif ($formDataData[ChallengeTask::DATA_IMAGE]['type'] == 'encoded') {
                $response = $this->imageStorage->upload($formDataData[ChallengeTask::DATA_IMAGE]['src']);
                $ctData[ChallengeTask::DATA_IMAGE] = isset($response['id']) ? $response['id'] : null;
            }
        }

        $challengeTask->setData($ctData);
    }

    /**
     * @param ChallengeTask $challengeTask
     * @param array $formDataData
     */
    private function processFormSortable(ChallengeTask $challengeTask, array $formDataData): void
    {
        $ctData = $challengeTask->getData();

        $ctData[ChallengeTask::DATA_SORTABLE] = [];

        if (isset($formDataData[ChallengeTask::DATA_SORTABLE])) {
            foreach ($formDataData[ChallengeTask::DATA_SORTABLE] as $image) {
                if ($image['type'] == 'url') {
                    $ctData[ChallengeTask::DATA_SORTABLE][] = [
                        ChallengeTask::DATA_IMAGE => Cloudinary::parseId($image['url']),
                        'id' => $image['id'],
                    ];
                } elseif ($image['type'] == 'encoded') {
                    $response = $this->imageStorage->upload($image['url']);

                    if (isset($response['id'])) {
                        $ctData[ChallengeTask::DATA_SORTABLE][] = [
                            ChallengeTask::DATA_IMAGE => $response['id'],
                            'id' => $image['id'],
                        ];
                    }
                }
            }
        }

        $challengeTask->setData($ctData);
    }

    /**
     * @param ChallengeTask $challengeTask
     * @param array $formDataData
     */
    private function processFormAnswer(ChallengeTask $challengeTask, array $formDataData): void
    {
        $ctData = $challengeTask->getData();
        $ctData[ChallengeTask::DATA_ANSWER] = $formDataData[ChallengeTask::DATA_ANSWER];
        $challengeTask->setData($ctData);
    }

    /**
     * @param ChallengeTask $challengeTask
     * @param array $formDataData
     */
    private function processFormAnswers(ChallengeTask $challengeTask, array $formDataData): void
    {
        $ctData = $challengeTask->getData();

        $ctData[ChallengeTask::DATA_ANSWERS] = [];

        if (isset($formDataData[ChallengeTask::DATA_ANSWERS])) {
            foreach ($formDataData[ChallengeTask::DATA_ANSWERS] as $answer) {
                if ($answer['image']['type'] == 'url') {
                    $url = Cloudinary::parseId($answer['image']['src']);
                } elseif ($answer['image']['type'] == 'encoded') {
                    $response = $this->imageStorage->upload($answer['image']['src']);
                    $url = (isset($response['id'])) ? $response['id'] : null;
                } else {
                    $url = null;
                }

                $ctData[ChallengeTask::DATA_ANSWERS][] = [
                    'id' => $answer['id'],
                    'answer' => !empty($answer['answer']) ? $answer['answer'] : null,
                    'checked' => $answer['checked'],
                    'image' => $url,
                ];
            }
        }

        $challengeTask->setData($ctData);
    }

    /**
     * @param ChallengeTask $challengeTask
     * @param array $formDataData
     */
    private function processFormVideo(ChallengeTask $challengeTask, array $formDataData): void
    {
        $ctData = $challengeTask->getData();
        $ctData[ChallengeTask::DATA_VIDEO] = $formDataData[ChallengeTask::DATA_VIDEO];
        $challengeTask->setData($ctData);
    }

}
