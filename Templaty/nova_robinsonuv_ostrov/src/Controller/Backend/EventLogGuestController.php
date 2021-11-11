<?php

namespace App\Controller\Backend;

use App\Entity\GuestEventLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EventLogGuestController
 * @package App\Controller\Backend
 */
class EventLogGuestController extends BackendController
{

    /**
     * @Route("/events/guest", name="eventlog-guest")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Logging / Guest',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'guest' => [
                        'caption' => 'Guest',
                    ],
                    'type' => [
                        'caption' => 'Type',
                    ],
                    'created_at' => [
                        'caption' => 'Created At',
                        'type' => 'date',
                    ],
                    '_actions' => [
                        'actions' => [
                            'view' => $this->generateUrl('eventlog-guest-view', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [],
            ],
        ]);
    }

    /**
     * @Route("/events/guest/view/{id}", name="eventlog-guest-view")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function view(Request $request, int $id)
    {
        /** @var \App\Entity\GuestEventLog $event */
        $event = $this->getDoctrine()
            ->getRepository(GuestEventLog::class)
            ->find($id);

        if (empty($event)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        return $this->render('Backend/view/eventlog.html.twig', [
            'layout' => 'guest',
            'id' => $event->getId(),
            'guest' => $event->getGuest()->getName(),
            'type' => $event->getType(),
            'data' => $event->getData(),
            'created_at' => $event->getCreatedAt(),
        ]);
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    private function getGridData(): array
    {
        /** @var GuestEventLog[] $events */
        $events = $this->getDoctrine()
            ->getRepository(GuestEventLog::class)
            ->findAll();

        if (empty($events)) {
            return [];
        }

        $data = [];

        foreach ($events as $item) {
            $data[] = [
                'id' => $item->getId(),
                'guest' => $item->getGuest()->getName(),
                'type' => $item->getType(),
                'created_at' => $item->getCreatedAt(),
            ];
        }

        return $data;
    }
}
