<?php

namespace App\EventListener;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class ApplicationChangerNotifier extends AbstractController
{
    #[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Application::class)]
    public function preUpdate(Application $application, PreUpdateEventArgs $event): void
    {
        $transport = Transport::fromDsn('native://default');
        $mailer = new Mailer($transport);

        $users = $application->getUsers();
        if (0 == count($users)) {
            return;
        }

        $application = $event->getObject();
        $changes = $event->getEntityChangeSet();

        $subject = 'Méteo des services - équipe PAS - Changements sur : '.$application->getTitle();
        $text = 'Application : '.$application->getTitle().PHP_EOL.PHP_EOL;

        foreach ($changes as $field => $value) {
            $text .= "Pour le champ \"$field\"".PHP_EOL.PHP_EOL;
            $text .= "Nouvelle valeur : {$value[1]}".PHP_EOL;
            $text .= "Ancienne valeur du champ : {$value[0]}".PHP_EOL.PHP_EOL;
        }

        $email = (new Email())
            ->from('no-reply@univ-paris1.fr')
            ->subject($subject)
            ->text($text);

        foreach ($users as $user) {
            $email->to($user->getMail());
            $mailer->send($email);
        }
    }
}
