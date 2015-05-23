<?php

namespace Fango\MainBundle\Command;

use Hip\MandrillBundle\Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MailerCommand
 * @author Farhad Safarov <http://ferhad.in>
 * @package Fango\MainBundle\Command
 */
class MailerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fango:mailer')
            ->setDescription('Fango mailer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dispatcher = $this->getContainer()->get('hip_mandrill.dispatcher');
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $mails = $em
            ->getRepository('FangoMainBundle:Mail')
            ->findBy(['status' => 'new']);

        foreach ($mails as $mail) {
            $message = new Message();

            $message
                ->setFromEmail('hello@fango.me')
                ->setFromName('Fango.me')
                ->addTo($mail->getEmail())
                ->setSubject('Test')
                ->setHtml('<html><body><h1>Some Content</h1></body></html>')
            ;

            $result = $dispatcher->send($message);

            $mail->setStatus($result[0]['status']);
            $mail->setMandrillId($result[0]['_id']);
            $mail->setRejectReason($result[0]['reject_reason']);

            $em->persist($mail);
            $em->flush();
        }

        $output->writeLn('Done!');
    }
}