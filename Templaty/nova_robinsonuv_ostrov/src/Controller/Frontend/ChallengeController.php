<?php

namespace App\Controller\Frontend;
use App\Entity\Challenge;
use App\Entity\ChallengeTask;
use App\Entity\Guest;
use App\Entity\GuestChallenge;
use App\Entity\GuestEventLog;
use App\Entity\GuestFile;
use App\Utils\RegistryHelper;
use App\Utils\RequestDumper;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ChallengeController extends Controller
{

    /**
     * @Route(
     *     name="challenge_form",
     *     path="/challenge/{slug}"
     * )
     * @param string $slug
     * @param Guest $user
     * @return Response
     */
    public function form(
        string $slug,
        Request $request,
        UserInterface $user,
        ManagerRegistry $registry,
        TranslatorInterface $t
    ) {
        try {
            $challenge = $this->loadChallenge($slug, $registry);
        } catch(\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Challenge not found');
        }
        /** @var GuestChallenge $guestChallenge */
        $guestChallenge = $registry->getRepository(GuestChallenge::class)
            ->findOneBy([ 'guest' => $user, 'challenge' => $challenge]);
        if (!$guestChallenge) {
            $guestChallenge = new GuestChallenge();
            $guestChallenge->setGuest($user);
            $guestChallenge->setChallenge($challenge);
            RegistryHelper::store([$guestChallenge], $registry);
        }
        if (!$guestChallenge->isFinished() && !$challenge->isValid()) {
            $this->addFlash('warning', $t->trans('Challenge has ended.'));
            return $this->redirectToRoute('challenge_form', ['slug' => $challenge->getUrlcode()]);
        }

        return $this->render(
            'Frontend/view/challenge.html.twig',
            [
                'challenge' => $challenge,
                'processUrl' => $this->generateUrl('challenge_process', ['slug' => $slug]),
                'data' => $this->prepareData($challenge, $guestChallenge, $t),
            ]
        );
    }

    /**
     * @Route(
     *     name="challenge_process",
     *     path="/challenge/process/{slug}",
     *     methods="POST"
     * )
     * @param string $slug
     * @param Guest $user
     * @return Response
     */
    public function process(
        string $slug,
        Request $request,
        UserInterface $user,
        ManagerRegistry $registry,
        TranslatorInterface $t
    ) {
        // get answers
        $answers = $request->request->get('answers');

        if (empty($answers) || !is_array($answers)) {
            return $this->json(null, 400);
        }

        try {
            $challenge = $this->loadChallenge($slug, $registry);
        } catch(\Doctrine\ORM\NoResultException $e) {
            return $this->json(null, 404);
        }
        if (!$challenge->isValid()) {
            return $this->json(['success' => false, 'message' => $t->trans('Challenge has ended.')]);
        }

        /** @var GuestChallenge $guestChallenge */
        $guestChallenge = $registry->getRepository(GuestChallenge::class)
            ->findOneBy([ 'guest' => $user, 'challenge' => $challenge]);
        if (!$guestChallenge) {
            return $this->json(['success' => false, 'message' => $t->trans('Error occurred, please refresh the page.')]);
        }
        if ($guestChallenge->isFinished()) {
            return $this->json(['success' => false, 'message' => $t->trans('You have already finished this challenge.')]);
        }

        // save answers
        /** @var ChallengeTask $task */
        foreach ($challenge->getTasks() as $task) {
            $type = $task->getType();
            $answer = isset($answers[$task->getId()]) ? $answers[$task->getId()] : null;

            if (($type == ChallengeTask::TYPE_PHOTO) || ($type == ChallengeTask::TYPE_VIDEO)) {
                $guestFile = (new GuestFile())
                    ->setType($type == ChallengeTask::TYPE_PHOTO ? 'image/jpeg' : 'video/mp4')
                    ->setGuest($user)
                    ->setGuestChallenge($guestChallenge)
                    ->setChallengeTask($task)
                    ->setUrl($answer);

                $registry->getManager()->persist($guestFile);
            } else {
                $guestChallenge->setTaskAnswer($task->getId(), $answer);
            }
        }

        $challenge->rate($guestChallenge);

        // store
        $log = new GuestEventLog();
        $log->setType(GuestEventLog::TYPE_CHALLENGE_FINISHED)
            ->setGuest($user)
            ->setData(array_merge(
                [
                    'answersId' => $guestChallenge->getId(),
                    'challengeId' => $challenge->getId(),
                ],
                RequestDumper::dump($request, [RequestDumper::USER_INFO])
            ));

        RegistryHelper::store([$guestChallenge, $log], $registry);

        // @todo cache invalidation?

        return $this->json([
            'success' => true,
            'data' => [
                'score' => $guestChallenge->getScore(),
            ],
        ]);
    }

    /**
     * @param Challenge $challenge
     * @param GuestChallenge $answers
     * @param TranslatorInterface $t
     * @return array
     */
    private function prepareData(Challenge $challenge, GuestChallenge $answers, TranslatorInterface $t)
    {
        $data = [
            'isFinished' => $answers->isFinished(),
            'score' => $t->transChoice('1 point|%count% points', $answers->getScore()),
            'tasks' => []
        ];

        /** @var ChallengeTask $task */
        foreach ($challenge->getTasks() as $task) {
            $type = $task->getType();
            $description = $task->getDescription();
            $answer = $answers->getTaskAnswer($task->getId());
            $options = $task->getData();

            // remove right answer and randomize answers
            if (in_array($type, [
                ChallengeTask::TYPE_QUIZ_ABCD,
                ChallengeTask::TYPE_QUIZ_PHOTO,
                ChallengeTask::TYPE_SELECT_PHOTO,
                ChallengeTask::TYPE_GUESS,
            ])) {
                if (isset($options[ChallengeTask::DATA_ANSWERS])) {
                    foreach ($options[ChallengeTask::DATA_ANSWERS] as &$item) {
                        if (isset($item['checked']))
                            unset($item['checked']);
                    }

                    shuffle($options[ChallengeTask::DATA_ANSWERS]);
                }
            } elseif ($type == ChallengeTask::TYPE_ORDER_PHOTOS) {
                if (isset($options[ChallengeTask::DATA_SORTABLE])) {
                    shuffle($options[ChallengeTask::DATA_SORTABLE]);
                }
            } elseif ($type == ChallengeTask::TYPE_WORD) {
                if (isset($options[ChallengeTask::DATA_ANSWER]))
                    unset($options[ChallengeTask::DATA_ANSWER]);
            }

            // convert photo or video answer
            if ((($type == ChallengeTask::TYPE_PHOTO) || ($type == ChallengeTask::TYPE_VIDEO))
                && !empty($answer)) {
                /** @var GuestFile $file */
                $file = $this->getDoctrine()
                    ->getRepository(GuestFile::class)
                    ->find($answer);

                $answer = ($file) ? $file->getUrl() : null;
            }

            $data['tasks'][] = [
                'id' => $task->getId(),
                'type' => $task->getType(),
                'label' => $task->getCaption(),
                'description' => !empty($description) ? $description : null,
                'options' => $options,
                'answer' => !empty($answer) ? $answer : null,
            ];
        }

        return $data;
    }

    /**
     * @param string $slug
     * @param ManagerRegistry $registry
     * @return Challenge
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function loadChallenge(string $slug, ManagerRegistry $registry)
    {
        /** @var QueryBuilder $qb */
        $qb = $registry->getManager()->createQueryBuilder();
        return $qb->select('c')
            ->from(Challenge::class, 'c')
            ->where('c.urlcode = :slug')
            ->andwhere('c.validFrom <= :now')
            ->andwhere('c.active = :status')
            ->setParameters([
                'slug' => $slug,
                'now' => new \DateTime(),
                'status' => true,
            ])
            ->getQuery()
            ->getSingleResult();
    }
}
