<?php

namespace App\Controller\Backend;

use App\Entity\Challenge;
use App\Entity\GuestChallenge;
use App\Entity\GuestFile;
use App\Entity\StaffEventLog;
use App\Service\ImageStorageInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GuestChallengeController
 * @package App\Controller\Backend
 */
class GuestChallengeController extends BackendController
{
    /** @var array */
    private $miniatureForChecker = [
        'width' => 236,
        'height' => 200,
    ];

    /**
     * @Route("/guest-challenge", name="guest-challenge")
     * @return Response
     */
    public function index()
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Guest challenges',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'challenge' => [
                        'caption' => 'Challenge',
                    ],
                    'guest' => [
                        'caption' => 'Guest',
                    ],
                    'score' => [
                        'caption' => 'Score',
                    ],
                    'finished' => [
                        'caption' => 'Finished',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_YES_NO,
                    ],
                    'createdAt' => [
                        'caption' => 'Created',
                        'type' => 'datetime',
                    ],
                    '_actions' => [
                        'actions' => [
                            'view' => $this->generateUrl('guest-challenge-view', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [],
            ],
        ]);
    }

    /**
     * @Route("/guest-challenges/view/{id}", name="guest-challenge-view")
     * @return Response
     */
    public function view($id)
    {
        /** @var GuestChallenge $gc */
        $gc = $this->getDoctrine()
            ->getRepository(GuestChallenge::class)
            ->find($id);

        if (empty($gc)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        return $this->render('Backend/view/guest-challenge.html.twig', [
            'id' => $gc->getId(),
            'name' => $gc->getGuest()->getName(),
            'email' => $gc->getGuest()->getEmail(),
            'challenge' => $gc->getChallenge()->getName(),
            'score' => $gc->getScore(),
            'data' => $gc->getData(),
            'finished_at' => $gc->getFinishedAt(),
            'created_at' => $gc->getCreatedAt(),
        ]);
    }

    /**
     * @Route("/guest-challenges/check/images", name="guest-challenge-check-images")
     * @return Response
     */
    public function checkImages()
    {
        /** @var Challenge[] $challenge */
        $challenge = $this->getDoctrine()
            ->getRepository(Challenge::class)
            ->findAll();

        $challengeList = [];

        if ($challenge) {
            foreach ($challenge as $challengeItem) {
                $challengeList[$challengeItem->getId()] = $challengeItem->getName();
            }
        }

        return $this->render('Backend/view/images.html.twig', [
            'challenges' => $challengeList,
        ]);
    }

    /**
     * @Route("/guest-challenges/check/videos", name="guest-challenge-check-videos")
     * @return Response
     */
    public function checkVideos()
    {
        /** @var Challenge[] $challenge */
        $challenge = $this->getDoctrine()
            ->getRepository(Challenge::class)
            ->findAll();

        $challengeList = [];

        if ($challenge) {
            foreach ($challenge as $challengeItem) {
                $challengeList[$challengeItem->getId()] = $challengeItem->getName();
            }
        }

        return $this->render('Backend/view/video.html.twig', [
            'challenges' => $challengeList,
        ]);
    }

    // AJAX image/video methods ----------------------------------------------------------------------------------------

    /**
     * @Route("/guest-challenges/videos/get/{source}/{status}/{offset}/{limit}.json", name="guest-challenges-videos-get")
     * @param string|int $source
     * @param string $status
     * @param int $offset
     * @param int $limit
     * @return Response
     */
    public function getVideos($source, $status, $offset = 0, $limit = 6)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        /** @var GuestFile[] $result */
        $qb->select('gf, g')
            ->from(GuestFile::class, 'gf')
            ->leftJoin('gf.guest', 'g')
            ->leftJoin('gf.guestChallenge', 'gc')
            ->leftJoin('gc.challenge', 'c')
            ->where('gf.type = :type')
            ->setParameter('type', ['video/mpeg'])
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($source != 'all') {
            $qb->where('c.id = :challenge')
                ->setParameter('challenge', $source);
        }
        if ($status != 'all') {
            $qb->andWhere('gf.status = :status')
                ->setParameter('status', GuestFile::convertStatusInt($status));
        }

        $result = $qb->getQuery()->getResult();
        $data = [];

        if ($result) {
            foreach ($result as $resultItem) {
                $data[] = [
                    'id' => $resultItem->getId(),
                    // @todo: videoInterface connection
                    'src' => $resultItem->getUrl(),
                    'title' => $resultItem->getGuest()->getName(),
                    'status' => GuestFile::convertStatusString($resultItem->getStatus()),
                    'challenge' => $resultItem->getGuestChallenge()->getChallenge()->getId(),
                ];
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/guest-challenges/images/get/{source}/{status}/{offset}/{limit}.json", name="guest-challenges-images-get")
     * @param ImageStorageInterface $imageStorage
     * @param string|int $source
     * @param string $status
     * @param int $offset
     * @param int $limit
     * @return Response
     */
    public function getImages(ImageStorageInterface $imageStorage, $source, $status, $offset = 0, $limit = 18)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        /** @var GuestFile[] $result */
        $qb->select('gf, g')
            ->from(GuestFile::class, 'gf')
            ->leftJoin('gf.guest', 'g')
            ->leftJoin('gf.guestChallenge', 'gc')
            ->leftJoin('gc.challenge', 'c')
            ->where('gf.type = :type')
            ->setParameter('type', ['image/jpeg'])
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($source != 'all') {
            $qb->where('c.id = :challenge')
                ->setParameter('challenge', $source);
        }
        if ($status != 'all') {
            $qb->andWhere('gf.status = :status')
                ->setParameter('status', GuestFile::convertStatusInt($status));
        }

        $result = $qb->getQuery()->getResult();
        $data = [];

        if ($result) {
            foreach ($result as $resultItem) {
                $data[] = [
                    'id' => $resultItem->getId(),
                    'src' => $imageStorage->get($resultItem->getUrl()),
                    'srcMiniature' => $imageStorage->resize(
                        $resultItem->getUrl(),
                        $this->miniatureForChecker['width'],
                        $this->miniatureForChecker['height'],
                        [
                            'format' => 'jpg',
                            'crop' => 'fill',
                        ]
                    ),
                    'title' => $resultItem->getGuest()->getName(),
                    'status' => GuestFile::convertStatusString($resultItem->getStatus()),
                    'challenge' => $resultItem->getGuestChallenge()->getChallenge()->getId(),
                ];
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/guest-challenges/content-item/reject/{id}", name="guest-challenges-content-item-reject")
     * @param LoggerInterface $logger
     * @param UserInterface $user
     * @param int $id
     * @return Response
     */
    public function rejectItem(LoggerInterface $logger, UserInterface $user, $id)
    {
        return $this->changeGuestFileStatusHelper($logger, $user, $id, GuestFile::STATUS_REJECTED);
    }

    /**
     * @Route("/guest-challenges/content-item/approve/{id}", name="guest-challenges-content-item-approve")
     * @param LoggerInterface $logger
     * @param UserInterface $user
     * @param int $id
     * @return Response
     */
    public function approveItem(LoggerInterface $logger, UserInterface $user, $id)
    {
        return $this->changeGuestFileStatusHelper($logger, $user, $id, GuestFile::STATUS_APPROVED);
    }

    /**
     * @Route("/guest-challenges/content-item/approve-many/{ids}", name="guest-challenges-content-item-approve-many")
     * @param LoggerInterface $logger
     * @param UserInterface $user
     * @param string $ids Comma separated ids
     * @return Response
     */
    public function approveItems(LoggerInterface $logger, UserInterface $user, $ids)
    {
        try {
            if (empty($ids))
                throw new \InvalidArgumentException('Invalid argument has been entered.');

            $this->getDoctrine()->getManager()
                ->createNativeQuery('UPDATE guest_file SET status=1 WHERE id IN (:ids)', new ResultSetMapping())
                ->setParameter('status', GuestFile::STATUS_APPROVED)
                ->setParameter('ids', explode(',', $ids))
                ->getResult();

            $event = (new StaffEventLog())
                ->setStaff($user)
                ->setAction(StaffEventLog::ACTION_UPDATE)
                ->setResource(GuestFile::class)
                ->setData([
                    'ids' => $ids,
                    'status' => GuestFile::STATUS_APPROVED,
                ]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json(['status' => 'false']);
        }

        return $this->json(['status' => true]);
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @param LoggerInterface $logger
     * @param UserInterface $user
     * @param int $id
     * @param int $status
     * @return Response
     */
    private function changeGuestFileStatusHelper(LoggerInterface $logger, UserInterface $user, $id, $status)
    {
        try {
            if (empty($id))
                throw new \InvalidArgumentException('Invalid argument has been entered.');

            /** @var GuestFile $guestFile */
            $guestFile = $this->getDoctrine()
                ->getRepository(GuestFile::class)
                ->find($id);

            if (empty($guestFile)) {
                throw new \InvalidArgumentException("Can not find item #$id.");
            }

            $guestFile->setStatus($status);
            $guestChallenge = $guestFile->getGuestChallenge();
            $guestChallenge->setTaskAnswer($guestFile->getChallengeTask()->getId(), $status);
            $guestFile->getChallengeTask()->getChallenge()->rate($guestChallenge);
            $event = (new StaffEventLog())
                ->setStaff($user)
                ->setAction(StaffEventLog::ACTION_UPDATE)
                ->setResource(GuestFile::class)
                ->setData([
                    'id' => $id,
                    'status' => $status,
                ]);

            $em = $this->getDoctrine()->getManager();
            $em->persist($guestFile);
            $em->persist($guestChallenge);
            $em->persist($event);
            $em->flush();

        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json(['status' => 'false']);
        }

        return $this->json(['status' => true]);
    }

    /**
     * @return array
     */
    private function getGridData(): array
    {
        /** @var GuestChallenge[] $guestChallenge */
        $guestChallenge = $this->getDoctrine()
            ->getRepository(GuestChallenge::class)
            ->findAll();

        if (empty($guestChallenge)) {
            return [];
        }

        $data = [];

        foreach ($guestChallenge as $item) {
            $data[] = [
                'id' => $item->getId(),
                'challenge' => $item->getChallenge()->getName(),
                'guest' => $item->getGuest()->getName() . ' (' . $item->getGuest()->getEmail() .')',
                'score' => $item->getScore(),
                'finished' => ($item->getFinishedAt() !== null),
                'createdAt' => $item->getCreatedAt(),
            ];
        }

        return $data;
    }

}
