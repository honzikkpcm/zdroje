<?php

namespace App\Service;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

/**
 * Class SmartEmailingAdapter
 * @package App\Service
 */
class SmartEmailingAdapter implements MessengerInterface
{
    const
        SETTING_STORE = 'store',
        SETTING_DEFAULT_FROM = 'from',
        SETTING_FRONT = 'front',
        SETTING_TAG = 'tag';

    /** @var \App\Service\SmartEmailing */
    private $driver;

    /** @var Environment */
    private $template;

    /** @var EntityManagerInterface */
    private $em;

    /** @var array */
    private $setting;

    /** @var Message */
    private $message;

    /** @var array */
    private $addList;

    /** @var array */
    private $removeList;

    /** @var array */
    private $sendList;

    /** @var bool */
    private $store = false;

    /** @var bool Indicated if front is enabled, it this mode only saves message to the database */
    private $front = false;

    /**
     * @param \App\Service\SmartEmailing $driver
     * @param Environment $template
     * @param EntityManagerInterface $em
     * @param \App\Service\Setting $settingService
     * @param array $setting
     */
    public function __construct(
        \App\Service\SmartEmailing $driver,
        Environment $template,
        EntityManagerInterface $em,
        \App\Service\Setting $settingService,
        array $setting = [])
    {
        if (isset($setting[self::SETTING_STORE])) {
            $this->store = (bool)$setting[self::SETTING_STORE];
        }
        if (isset($setting[self::SETTING_DEFAULT_FROM])) {
            $this->setting[self::SETTING_DEFAULT_FROM] = $setting[self::SETTING_DEFAULT_FROM];
        }
        if (isset($setting[self::SETTING_FRONT])) {
            $this->front = (bool)$setting[self::SETTING_FRONT];
        }

        $this->setting[self::SETTING_TAG] = (isset($setting[self::SETTING_TAG]))
            ? $setting[self::SETTING_TAG] : 'default';

        // get from setting service
        $this->addList = [
            Message::TYPE_GUEST_REGISTRATION => $settingService->get('smartemailing_list_registration'),
            Message::TYPE_GUEST_NOT_CLOSED_CHALLENGES => $settingService->get('smartemailing_list_not_closed_c'),
        ];
        $this->removeList = [
            Message::TYPE_GUEST_BANNED => $settingService->get('smartemailing_list_banned'),
        ];
        $this->sendList = [
            Message::TYPE_STAFF_REGISTRATION,
            Message::TYPE_STAFF_RESET_PASSWORD,
            Message::TYPE_GUEST_REGISTRATION,
            Message::TYPE_GUEST_VERIFY_REGISTRATION,
            Message::TYPE_GUEST_RESET_PASSWORD,
            Message::TYPE_GUEST_PASSWORD_CHANGED,
        ];

        $this->driver = $driver;
        $this->template = $template;
        $this->em = $em;
        $this->setting = $setting;
    }

    /**
     * Sends message and update Message object
     * According to configuration can store it to database
     *
     * @param Message $message
     */
    public function send(Message $message)
    {
        $this->message = $message;
        // update message
        $now = new \DateTime();
        $message->setDriver(SmartEmailing::class);
        $message->setCreatedAt($now);

        if (isset($this->setting['from'])) {
            $message->setFrom($this->setting['from']);
        }

        $type = $message->getType();
        // generate body
        if (in_array($type, $this->sendList)) {
            $body = $this->template->render("/Emails/$type.html.twig", $message->getParams());
            $message->setBody($body);
        } else {
            $message->setBody('<h1>Body will be generated on the server.</h1>');
        }

        if (!$this->front) {
            // send
            if (!empty($this->addList[$type])) {
                $this->driver->addToContactList($this->formatEmail($this->message->getTo()), $this->addList[$type]);
            }
            if (!empty($this->removeList[$type])) {
                $this->driver->deleteFromContactList($this->formatEmail($this->message->getTo()), $this->removeList[$type]);
            }
            if (in_array($type, $this->sendList)) {
                $this->driver->sendCustomEmail([
                    'sender' => $this->message->getFrom(),
                    'recipient' => $this->message->getTo(),
                    'tag' => $this->setting['tag'],
                    'subject' => $this->message->getSubject(),
                    'body' => $this->message->getBody(),
                ]);
            }

            // update message
            $message->setSent(true);
            $message->setSentAt($now);
        }

        if ($this->store || $this->front) {
            $this->em->persist($message);
            $this->em->flush();
        }
    }

    /**
     * Send a message which has been generated before and is waiting in the front
     *
     * @param Message $message
     * @return bool Returns false when the message belongs to different driver or have been sent already
     */
    public function sendFrontMessage(Message $message): bool
    {
        if ($message->getDriver() !== SmartEmailing::class)
            return false;
        if ($message->isSent())
            return false;

        $type = $message->getType();
        $now = new \DateTime();

        // send
        if (!empty($this->addList[$type])) {
            $this->driver->addToContactList($this->formatEmail($message->getTo()), $this->addList[$type]);
        }
        if (!empty($this->removeList[$type])) {
            $this->driver->deleteFromContactList($this->formatEmail($message->getTo()), $this->removeList[$type]);
        }
        if (in_array($type, $this->sendList)) {
            $this->driver->sendCustomEmail([
                'sender' => $message->getFrom(),
                'recipient' => $message->getTo(),
                'tag' => $this->setting['tag'],
                'subject' => $message->getSubject(),
                'body' => $message->getBody(),
            ]);
        }

        // update message
        $message->setSent(true);
        $message->setSentAt($now);
        $this->em->persist($message);
        $this->em->flush();

        return true;
    }

    /**
     * @param string $email
     * @return string
     */
    private function formatEmail(string $email): string
    {
        if (preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
            return trim($matches[2]);
        } else {
            return $email;
        }
    }
}
