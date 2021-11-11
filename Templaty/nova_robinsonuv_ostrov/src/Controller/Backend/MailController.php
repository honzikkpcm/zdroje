<?php

namespace App\Controller\Backend;

use App\Entity\Message;
use App\Service\MessengerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MailController
 * @package App\Controller\Backend
 */
class MailController extends BackendController
{

    /**
     * @Route("/mail", name="mail")
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function index(TranslatorInterface $translator): Response
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Mail',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'type' => [
                        'caption' => 'Type',
                        'replacement' => [
                            Message::TYPE_STAFF_REGISTRATION => $translator->trans('registration (staff)'),
                            Message::TYPE_STAFF_RESET_PASSWORD => $translator->trans('reset password (staff)'),
                            Message::TYPE_GUEST_REGISTRATION => $translator->trans('registration (guest)'),
                            Message::TYPE_GUEST_RESET_PASSWORD => $translator->trans('reset password (guest)'),
                        ],
                    ],
                    'to' => [
                        'caption' => 'To',
                    ],
                    'sent' => [
                        'caption' => 'Sent',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_YES_NO,
                    ],
                    'sent_at' => [
                        'caption' => 'Sent At',
                        'type' => 'datetime',
                    ],
                    'created_at' => [
                        'caption' => 'Created At',
                        'type' => 'datetime',
                    ],
                    '_actions' => [
                        'actions' => [
                            'send' => $this->generateUrl('mail-send', ['id' => '--id--']),
                            'view' => $this->generateUrl('mail-view', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [
                    ''
                ],
            ],
        ]);
    }

    /**
     * @Route("/mail/send/{id}", name="mail-send")
     * @param MessengerInterface $messenger
     * @param LoggerInterface $logger
     * @param int $id
     * @return Response
     */
    public function send(MessengerInterface $messenger, LoggerInterface $logger, $id): Response
    {
        /** @var \App\Entity\Message $message */
        $message = $this->getDoctrine()
            ->getRepository(Message::class)
            ->find($id);

        if (empty($message)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        try {
            $messenger->send($message);
            $this->addFlash('success', 'The message has been sent.');
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', 'Error during sending message.');
        }

        return $this->redirectToRoute('mail');
    }

    /**
     * @Route("/mail/view/{id}", name="mail-view")
     * @param int $id
     * @return Response
     */
    public function view(int $id): Response
    {
        /** @var \App\Entity\Message $message */
        $message = $this->getDoctrine()
            ->getRepository(Message::class)
            ->find($id);

        if (empty($message)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        return $this->render('Backend/view/message.html.twig', [
            'h1' => 'View message',
            'message' => $message->getBody(),
        ]);
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    private function getGridData(): array
    {
        /** @var Message[] $mails */
        $mails = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAll();

        if (empty($mails)) {
            return [];
        }

        $data = [];

        foreach ($mails as $item) {
            $data[] = [
                'id' => $item->getId(),
                'type' => $item->getType(),
                'to' => $item->getTo(),
                'sent' => $item->isSent(),
                'sent_at' => $item->getSentAt(),
                'created_at' => $item->getCreatedAt(),
            ];
        }

        return $data;
    }
}
