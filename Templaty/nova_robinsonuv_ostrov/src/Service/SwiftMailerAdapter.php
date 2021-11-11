<?php

namespace App\Service;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

/**
 * Class SwiftMailerAdapter
 * @package App\Service
 */
class SwiftMailerAdapter implements MessengerInterface
{
    const
        SETTING_STORE = 'store',
        SETTING_DEFAULT_FROM = 'from',
        SETTING_FRONT = 'front';

    /** @var \Swift_Mailer */
    private $driver;

    /** @var Environment */
    private $template;

    /** @var EntityManagerInterface */
    private $em;

    /** @var array */
    private $setting;

    /** @var Message */
    private $message;

    /** @var bool */
    private $store = false;

    /** @var bool Indicated if front is enabled, it this mode only saves message to the database */
    private $front = false;

    /**
     * @param \Swift_Mailer $driver
     * @param Environment $template
     * @param EntityManagerInterface $em
     * @param array $setting
     */
    public function __construct(\Swift_Mailer $driver, Environment $template, EntityManagerInterface $em, array $setting = [])
    {
        if (isset($setting[self::SETTING_STORE])) {
            $this->store = (bool)$setting[self::SETTING_STORE];
        }
        if (isset($setting[self::SETTING_DEFAULT_FROM])) {
            $this->setting['from'] = $setting[self::SETTING_DEFAULT_FROM];
        }
        if (isset($setting[self::SETTING_FRONT])) {
            $this->front = (bool)$setting[self::SETTING_FRONT];
        }

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
        $message->setDriver(\Swift_Mailer::class);
        $message->setCreatedAt($now);

        if (isset($this->setting['from'])) {
            $message->setFrom($this->setting['from']);
        }

        // generate body
        $type = $message->getType();
        $body = $this->template->render("/Emails/$type.html.twig", $message->getParams());
        $message->setBody($body);

        if (!$this->front) {
            // send
            $swiftMessage = (new \Swift_Message($this->message->getSubject()))
                ->setFrom(\App\Utils\Strings::formatEmails($this->message->getFrom()))
                ->setTo(\App\Utils\Strings::formatEmails($this->message->getTo()))
                ->setBody($body, 'text/html', 'utf-8');

            $cc = $this->message->getCc();

            if (!empty($cc)) {
                $swiftMessage->setCc(\App\Utils\Strings::formatEmails($cc));
            }

            $this->driver->send($swiftMessage);

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
        if ($message->getDriver() !== \Swift_Mailer::class)
            return false;
        if ($message->isSent())
            return false;

        $now = new \DateTime();

        // send
        $swiftMessage = (new \Swift_Message($message->getSubject()))
            ->setFrom(\App\Utils\Strings::formatEmails($message->getFrom()))
            ->setTo(\App\Utils\Strings::formatEmails($message->getTo()))
            ->setBody($message->getBody(), 'text/html', 'utf-8');

        $cc = $message->getCc();

        if (!empty($cc)) {
            $swiftMessage->setCc(\App\Utils\Strings::formatEmails($cc));
        }

        $this->driver->send($swiftMessage);

        // update message
        $message->setSent(true);
        $message->setSentAt($now);
        $this->em->persist($message);
        $this->em->flush();

        return true;
    }
}
