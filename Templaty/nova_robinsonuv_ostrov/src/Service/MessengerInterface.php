<?php

namespace App\Service;
use App\Entity\Message;

/**
 * Class MessengerInterface
 * @package App\Service
 */
interface MessengerInterface
{
    /**
     * Sends message and update Message object
     * According to configuration can store it to database
     *
     * @param Message $message
     */
    public function send(Message $message);

    /**
     * Send a message which has been generated before and is waiting in the front
     *
     * @param Message $message
     * @return bool Returns false when the message belongs to different driver or have been sent already
     */
    public function sendFrontMessage(Message $message): bool;

}
