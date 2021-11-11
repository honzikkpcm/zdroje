<?php

namespace App\Command;
use App\Entity\LockingFlag;
use App\Entity\Message;
use App\Service\MessengerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SendMessengerCommand
 * @package App\Command
 */
class SendMessengerCommand extends Command
{
    /** @var MessengerInterface */
    private $messenger;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param MessengerInterface $messenger
     */
    public function __construct(MessengerInterface $messenger, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->messenger = $messenger;
        $this->em = $em;
    }

    /**
     */
    protected function configure(): void
    {
        $this->setName('app:messenger:flush')
             ->setDescription('Flush all messages from the front')
             ->setHelp('This command send all messages which are waiting in the front');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Starting...');

        // lock
        try {
            $lock = (new LockingFlag())
                ->setKey('messenger:front')
                ->setResource(__METHOD__);

            $this->em->persist($lock);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            /** @var LockingFlag $currentLock */
            $currentLock = $this->em->getRepository(LockingFlag::class)
                ->findOneBy(['key' => 'messenger:front']);
            $resource = $currentLock->getResource();
            $date = $currentLock->getCreatedAt()->format('Y-m-d H:i:s');
            $output->writeln("Process is locked by $resource, started at: $date");
            return;
        }

        /** @var Message[] $messages */
        $messages = $this->em->getRepository(Message::class)
            ->findBy(['sent' => false]);

        if (!$messages) {
            $output->writeln('No message in the front');
            return;
        }

        $messagesNumber = count($messages);
        $output->writeln("$messagesNumber messages will be sent");

        foreach ($messages as $message) {
            if ($this->messenger->sendFrontMessage($message)) {
                $output->write('.');
            } else {
                $output->write('*');
            }
        }

        // delete lock
        $this->em->remove($lock);
        $this->em->flush();

        $output->writeln("\nDone");
    }
}