<?php

namespace Sygefor\Bundle\TraineeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ChangePasswordCommand.
 */
class ChangePasswordCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('sygefor:trainee:change-password')
            ->setDescription('Change the password of a trainee.')
            ->setDefinition(array(
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
            ));
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email    = $input->getArgument('email');
        $password = $input->getArgument('password');

        $em         = $this->getContainer()->get('doctrine')->getManager();
        $repository = $em->getRepository('SygeforTraineeBundle:AbstractTrainee');
        $trainee    = $repository->findOneByEmail($email);
        if ( ! $trainee) {
            throw new \InvalidArgumentException(sprintf('Trainee identified by "%s" email does not exist.', $email));
        }

        $factory = $this->getContainer()->get('security.encoder_factory');
        $encoder = $factory->getEncoder($trainee);
        $trainee->setPassword($encoder->encodePassword($password, $trainee->getSalt()));
        $em->flush();
        $output->writeln(sprintf('Changed password for trainee <comment>%s</comment>', $email));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ( ! $input->getArgument('email')) {
            $email = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please give the email:',
                function ($email) {
                    if (empty($email)) {
                        throw new \Exception('Email can not be empty');
                    }

                    return $email;
                }
            );
            $input->setArgument('email', $email);
        }

        if ( ! $input->getArgument('password')) {
            $password = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please enter the new password:',
                function ($password) {
                    if (empty($password)) {
                        throw new \Exception('Password can not be empty');
                    }

                    return $password;
                }
            );
            $input->setArgument('password', $password);
        }
    }
}
