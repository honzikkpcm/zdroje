<?php
/**
 * Created by PhpStorm.
 * User: rum
 * Date: 7.2.18
 * Time: 11:51
 */

namespace App\Command;

use App\Entity\Staff;
use App\Utils\RegistryHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class CreateAdminCommand extends Command
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * CreateAdminCommand constructor.
     * @param UserPasswordEncoderInterface $encoder
     * @param ValidatorInterface $validator
     * @param ManagerRegistry $registry
     */
    public function __construct(UserPasswordEncoderInterface $encoder, ValidatorInterface $validator, ManagerRegistry $registry)
    {
        $this->encoder = $encoder;
        $this->validator = $validator;
        $this->registry = $registry;
        parent::__construct();
    }


    /** @inheritdoc */
    protected function configure()
    {
        $this
            ->setName('app:admin:create')
            ->setDescription('Creates a new admin.')
            ->setHelp('This command new staff user with admin role.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $output->writeln('Admin user setup');

        $name = (string) $helper->ask($input, $output, new Question('Username:'));
        $email = (string) $helper->ask($input, $output, new Question('Email:'));
        $password = (string) $helper->ask(
            $input,
            $output,
            (new Question('Password:'))->setHidden(true)
        );
        $repeated = (string) $helper->ask(
            $input,
            $output,
            (new Question('Retype password:'))->setHidden(true)
        );

        if ($password == '') {
            $output->writeln('Password can not be empty.');
            return;
        }
        if ($password != $repeated) {
            $output->writeln('Passwords must match.');
            return;
        }
        $user = new Staff();
        $user->setActive(true);
        $user->setRole(Staff::ROLE_ADMIN);
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($this->encoder->encodePassword($user, $password));

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $output->writeln((string) $errors);
            return;
        }
        RegistryHelper::store([$user], $this->registry);
        $output->writeln(sprintf('User %s successfully created', $name));
    }
}
