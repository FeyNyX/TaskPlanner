<?php

namespace TaskPlannerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Validator\Constraints\DateTime;

//@todo Configure swiftmailer so that it sends emails that are not being filtered by spam filters.
//@todo Merge messages for a user to one message instead of sending multiple messages.
class ReminderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("email:reminder")
            ->setDescription("Send a reminder about unfinished tasks to a user.")
            ->addArgument(
                'date',
                InputArgument::OPTIONAL,
                'Date'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userRepo = $this->getContainer()->get('doctrine')->getRepository("TaskPlannerBundle:User");
        $taskRepo = $this->getContainer()->get('doctrine')->getRepository("TaskPlannerBundle:Task");

        $date = new \DateTime();
        $tasks = $taskRepo->findTasksToRemind($date);


        foreach ($tasks as $task) {
            $emailAddress = $userRepo->findOneBy(array("id" => $task->getUser()->getId()))->getEmail();

            //preparing email for the user
            $message = new \Swift_Message();
            $message
                ->setTo($emailAddress)
                ->setFrom("wojtek.lenartowicz@gmail.com")
                ->setBody($task->getUser() . ": " . $task->getName() . " " . $task->getToBeFinishedAt()->format('Y-m-d'))
                ->setSubject("Reminder!");

            //sending that email
            $container = $this->getContainer();
            $mailer = $container->get('mailer');

            $mailer->send($message);
        }

        //flushing emails
        $spool = $mailer->getTransport()->getSpool();
        $transport = $container->get('swiftmailer.transport.real');

        $spool->flushQueue($transport);
    }
}