<?php

namespace App\Controller\Backend;

use App\Entity\Message;
use App\Service\MessengerInterface;
use App\Service\Setting;
use App\Service\SmartEmailing;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MaintenanceController
 * @package App\Controller\Backend
 */
class MaintenanceController extends BackendController
{
    /**
     * @Route("/maintenance", name="maintenance")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('Backend/view/maintenance.html.twig', [
            'title' => 'Maintenance',
        ]);
    }

    /**
     * @Route("/maintenance/actions/inform-not-closed-challenges", name="maintenance-inform-not-closed-challenges")
     * @param Request $request
     * @param MessengerInterface $messenger
     * @param TranslatorInterface $t
     * @param LoggerInterface $logger
     * @return Response
     */
    public function informNotClosedChallenges(
        Request $request,
        MessengerInterface $messenger,
        TranslatorInterface $t,
        LoggerInterface $logger): Response
    {
        try {
            $conn = $this->getDoctrine()->getConnection();
            $stmt = $conn->prepare('SELECT email FROM ( '
                .'SELECT email,COUNT(guest_id) FROM guest_challenge gc '
                .'LEFT JOIN guest g ON gc.guest_id=g.id '
                .'WHERE finished_at IS NOT NULL '
                .'GROUP BY email) AS q1 '
                .'WHERE count = ('
                .'SELECT COUNT(id) FROM challenge '
                .'WHERE valid_from <= NOW() AND active=true)');
            $stmt->execute();
            $guests = $stmt->fetchAll();

            if (!empty($guests)) {
                $message = (new Message())
                    ->setType(Message::TYPE_GUEST_NOT_CLOSED_CHALLENGES)
                    ->setSubject($t->trans('Challenges is ending'));

                foreach ($guests as $guest) {
                    $m = clone $message;
                    $m->setTo($guest->email);
                    $messenger->send($m);
                }
            }
            $this->addFlash('success', $t->trans('The messages have been sent successfully.'));

        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', $t->trans('Unknown error'));
        }

        return $this->redirectToRoute('maintenance');
    }

}
