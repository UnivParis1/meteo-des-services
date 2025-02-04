<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\AddApplicationFormType;
use App\Service\ApplicationService;
use App\Service\MaintenanceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends AbstractController
{
    public function __construct(public ApplicationService $applicationService,
                                public MaintenanceService $maintenanceService)
    {
    }

    #[Route('/application/add', name: 'app_add_application')]
    public function add(Request $request): Response
    {
        $application = new Application();
        $form = $this->createForm(AddApplicationFormType::class, $application);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->applicationService->createApplicationFromMeteoDesServices($application);
            $this->addFlash('success', 'Application ajoutée avec succès');
            return $this->redirectToRoute('app_meteo', ['page' => 1]);
        }

        return $this->render('application_form/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/application/edit/{id}', name: 'app_edit_application')]
    public function edit(Request $request, int $id): Response
    {
        $application = $this->applicationService->getApplicationById($id);
        $maintenances = $this->maintenanceService->getNextMaintenancesFromApplication($id);
        $form = $this->createForm(AddApplicationFormType::class, $application);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->applicationService->updateApplication($application);
            $this->addFlash('success', 'Modification effectuée avec succès');
            return $this->redirectToRoute('app_meteo', ['page' => 1]);
        }

        return $this->render('application_form/index.html.twig', [
            'form' => $form->createView(),
            'maintenances' => $maintenances,
            'edit' => true,
            'applicationId' => $application->getId()
        ]);
    }

    #[Route('/application/delete/{id}', name: 'app_delete_application')]
    public function delete(int $id, Request $request): Response
    {
        $application = $this->applicationService->getApplicationById($id);
        if ($application != null) {
            $this->applicationService->deleteApplication($application);
            $this->addFlash('success', 'Application supprimée avec succès');
        } else {
            $this->addFlash('error', 'Application non trouvée');
        }

        return $this->redirectToRoute('app_meteo', ['page' => 1]);
    }
}
