<?php

namespace App\Controller;

use App\Form\NotifyProblemFormType;
use App\Model\NotifyProblem;
use App\Repository\ApplicationRepository;
use Exception;
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
    public function __construct(private ApplicationRepository $applicationRepository) {}

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/signaler', name: 'app_notify_problem')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $problem = new NotifyProblem();

        $form = $this->createForm(NotifyProblemFormType::class, $problem);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $application = $this->applicationRepository->findOneBy(['title' => $problem->title]);

            if ($application == null)
                throw new Exception("Veuillez contacter la DSIUN-PAS, l'application n'existe pas en Base de données");

            $notifyUser = $application->getUser();

            $text = "L'utilisateur ayant pour identifiant: " . $this->getUser()->getUserIdentifier() . " a signalé un disfonctionnement sur l'application : " . $problem->title . PHP_EOL . PHP_EOL;
            $text .= "Description renseignée par l'utilisateur : ". PHP_EOL . $problem->message . PHP_EOL;

            $subject = "Méteo des services - équipe PAS - " . $problem->title;

            $email = (new TemplatedEmail())
                ->from('no-reply@univ-paris1.fr')
                ->subject($subject)
                ->htmlTemplate('emails/email_template.html.twig')
                ->context([
                    'subject' => $subject,
                    'message' => $text,
                ]);

            if ($notifyUser && $notifyUser->isRecevoirMail()) {
                $email->cc($notifyUser->getMail());
            }
            $email->to('assistance-dsiun@univ-paris1.fr');

            $mailer->send($email);
            $this->addFlash('success', 'Problème signalé avec succès');
            return $this->redirectToRoute('app_meteo');
        } else {
            return $this->render('notify_problem/index.html.twig', [
                'controller_name' => 'NotifyProblemController',
                'form' => $form->createView()
            ]);
        }
    }
}
