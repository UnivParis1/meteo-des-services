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
            if ($problem->message == '') {
                $text = "Problème signalé sur l'application" . " " . $problem->title;
            } else {
                $text = '" ' . $problem->message . ' "';
            }
            $email = (new TemplatedEmail())
                ->subject('Méteo des services : signalement')
                ->htmlTemplate('emails/email_template.html.twig')
                ->context([
                    'subject' => 'Signalement d\'un problème avec l\'application ' . $problem->title,
                    'message' => $text,
                ]);

            $env = $this->getParameter('kernel.environment');
            switch ($env) {
                case "dev":
                    $email->from(new Address('ebohm@univ-paris1.fr', 'Méteo des Services DEV'));
                    $email->to('Etienne.Bohm@univ-paris1.fr');
                    break;
                case "test":
                    $email->from(new Address('no-reply@univ-paris1.fr', 'Méteo des Services TEST'));
                    $email->to("Marc-Olivier.Lagadic@univ-paris1.fr");
                    break;
            }

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Problème signalé avec succès');
                return $this->redirectToRoute('app_meteo');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'Erreur lors de la transmission de votre signalement');
                return $this->redirectToRoute('app_meteo');
            }
        }

        return $this->render('notify_problem/index.html.twig', [
            'controller_name' => 'NotifyProblemController',
            'form' => $form->createView()
        ]);
    }
}
