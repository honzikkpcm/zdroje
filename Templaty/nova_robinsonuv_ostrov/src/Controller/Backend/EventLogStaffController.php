<?php

namespace App\Controller\Backend;

use App\Entity\StaffEventLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EventLogStaffController
 * @package App\Controller\Backend
 */
class EventLogStaffController extends BackendController
{

    /**
     * @Route("/events/staff", name="eventlog-staff")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Logging / Staff',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'staff' => [
                        'caption' => 'Staff',
                    ],
                    'resource' => [
                        'caption' => 'Resource',
                    ],
                    'action' => [
                        'caption' => 'Action',
                    ],
                    'created_at' => [
                        'caption' => 'Created At',
                        'type' => 'date',
                    ],
                    '_actions' => [
                        'actions' => [
                            'view' => $this->generateUrl('eventlog-staff-view', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [],
            ],
        ]);
    }

    /**
     * @Route("/events/staff/view/{id}", name="eventlog-staff-view")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function view(Request $request, int $id)
    {
        /** @var \App\Entity\StaffEventLog $event */
        $event = $this->getDoctrine()
            ->getRepository(StaffEventLog::class)
            ->find($id);

        if (empty($event)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        return $this->render('Backend/view/eventlog.html.twig', [
            'layout' => 'staff',
            'id' => $event->getId(),
            'staff' => $event->getStaff()->getName(),
            'resource' => $event->getResource(),
            'action' => $event->getAction(),
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
        /** @var StaffEventLog[] $events */
        $events = $this->getDoctrine()
            ->getRepository(StaffEventLog::class)
            ->findAll();

        if (empty($events)) {
            return [];
        }

        $data = [];

        foreach ($events as $item) {
            $data[] = [
                'id' => $item->getId(),
                'staff' => $item->getStaff()->getName(),
                'resource' => $item->getResource(),
                'action' => $item->getAction(),
                'created_at' => $item->getCreatedAt(),
            ];
        }

        return $data;
    }
}
