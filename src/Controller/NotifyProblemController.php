<?php

namespace App\Controller;

use App\Form\NotifyProblemFormType;
use App\Model\NotifyProblem;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class NotifyProblemController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/signaler', name: 'app_notify_problem')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $problem = new NotifyProblem();
        $form = $this->createForm(NotifyProblemFormType::class, $problem);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $text = "Problème signalé sur l'application" . " " . $problem->title . PHP_EOL;
            $text .= ($problem->message == '') ? "Aucune description renseignée" : $problem->message . PHP_EOL;

            $subject = "Méteo des services - équipe PAS - " . $problem->title;

            $email = (new TemplatedEmail())
                ->subject($subject)
                ->htmlTemplate('emails/email_template.html.twig')
                ->context([
                    'subject' => $subject,
                    'message' => $text
                ]);

            $toSend = false;
            $env = $this->getParameter('kernel.environment');

            if ($env == "dev" || $env == "test") {
                $from = $this->getUser()->getUserIdentifier() ."@univ-paris1.fr";

                $email->from(new Address($from, "Méteo des Services " . strtoupper($env)));

                if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                    $toSend = true;
                    $email->to($from);
                } else {
                    $this->addFlash("error", "Application en test pas d'envoi de mails");
                }
            } else {
                throw new \Exception("Environnement autre que dev/test non configurés");
            }

            if ($toSend) {
                try {
                    $mailer->send($email);
                    $this->addFlash('success', 'Problème signalé avec succès');
                }
                catch (TransportExceptionInterface $e) {
                    $this->addFlash('error', 'Erreur lors de la transmission de votre signalement : ' . $e->getMessage());
                }
            }
            return $this->redirectToRoute('app_meteo');
        } else {
            return $this->render('notify_problem/index.html.twig', [
                'controller_name' => 'NotifyProblemController',
                'form' => $form->createView()
            ]);
        }
    }
}
