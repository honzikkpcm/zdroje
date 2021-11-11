<?php

namespace App\Controller\Backend;

use App\Entity\Guest;
use App\Entity\GuestFile;
use App\Entity\StaffEventLog;
use App\Twig\JsonGridExtension;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class GuestController
 * @package App\Controller\Backend
 */
class GuestController extends BackendController
{

    /**
     * @Route("/guest", name="guest")
     * @return Response
     */
    public function index(TranslatorInterface $t): Response
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Guest',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'name' => [
                        'caption' => 'Name',
                    ],
                    'email' => [
                        'caption' => 'Email',
                    ],
                    'verified' => [
                        'caption' => 'Verified',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_YES_NO,
                    ],
                    'facebook' => [
                        'caption' => 'Facebook User',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_YES_NO,
                    ],
                    'banned' => [
                        'caption' => 'Banned',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_YES_NO,
                    ],
                    'createdAt' => [
                        'caption' => 'Created At',
                        'type' => 'datetime',
                    ],
                    '_actions' => [
                        'actions' => [
                            'edit' => $this->generateUrl('guest-edit', ['id' => '--id--']),
                            'view' => $this->generateUrl('guest-view', ['id' => '--id--']),
                            'ban' => $this->generateUrl('guest-ban', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [
                    JsonGridExtension::SETTING_ADDITIONAL_ACTIONS => [
                        'ban' => [
                            'icon' => 'ban',
                            'key' => true,
                            'title' => $t->trans('ban'),
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @Route("/guest/edit/{id}", name="guest-edit")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     * @todo
     */
    public function edit(Request $request, int $id)
    {
        /** @var \App\Entity\Guest $guest */
        $guest = $this->getDoctrine()
            ->getRepository(Guest::class)
            ->find($id);

        if (empty($guest)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        return $this->render('Backend/view/modal.html.twig', [
            'h1' => 'Edit',
        ]);
    }

    /**
     * @Route("/guest/view/{id}", name="guest-view")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function view(Request $request, int $id)
    {
        /** @var \App\Entity\Guest $guest */
        $guest = $this->getDoctrine()
            ->getRepository(Guest::class)
            ->find($id);

        if (empty($guest)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        $stmt = $this->getDoctrine()->getConnection()->prepare('SELECT number,name,score,finished_at '
            .'FROM guest_challenge gc '
            .'LEFT JOIN challenge c ON gc.challenge_id=c.id '
            .'WHERE active=true AND guest_id=?');
        $stmt->execute([$id]);
        $challenges = $stmt->fetchAll();

        // total score
        $total = 0;

        if ($challenges) {
            foreach ($challenges as $item) {
                $total += $item['score'];
            }
        }

        return $this->render('Backend/view/guest-view.html.twig', [
            'guest' => $guest,
            'challenges' => $challenges,
            'total' => $total,
        ]);
    }

    /**
     * @Route("/guest/ban/{id}", name="guest-ban")
     * @param int $id
     * @param LoggerInterface $logger
     * @param UserInterface $user
     * @return Response
     */
    public function ban(int $id, LoggerInterface $logger, UserInterface $user): Response
    {
        /** @var Guest $guest */
        $guest = $this->getDoctrine()
            ->getRepository(Guest::class)
            ->find($id);

        if (empty($guest)) {
            $this->addFlash('warning', "Can not find item #$id.");
            return $this->redirectToRoute('guest');
        }

        if ($guest->isBanned()) {
            $this->addFlash('warning', 'The guest has been banned already.');
            return $this->redirectToRoute('guest');
        }

        $guest->setBanned(true);
        $event = (new StaffEventLog())
            ->setStaff($user)
            ->setAction(StaffEventLog::ACTION_UPDATE)
            ->setResource(Guest::class)
            ->setData([
                'id' => $id,
                'before' => [
                    'banned' => false,
                ],
                'after' => [
                    'banned' => true,
                ],
            ]);

        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($guest);
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'The guest has been banned.');
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', "Can not ban item #$id.");
        }

        return $this->redirectToRoute('guest');
    }

    /**
     * @return array
     */
    private function getGridData(): array
    {
        /** @var Guest[] $guests */
        $guests = $this->getDoctrine()
            ->getRepository(Guest::class)
            ->findAll();

        if (empty($guests)) {
            return [];
        }

        $data = [];

        foreach ($guests as $item) {
            $data[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'email' => $item->getEmail(),
                'facebook' => !empty($item->getFacebookId()),
                'banned' => $item->isBanned(),
                'verified' => $item->isVerified(),
                'createdAt' => $item->getCreatedAt(),
            ];
        }

        return $data;
    }
}
