<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\MaintenanceType;
use App\Service\ApplicationService;
use App\Service\MaintenanceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends AbstractController
{
    public function __construct(public ApplicationService $applicationService,
                                public MaintenanceService $maintenanceService)
    {
    }

    #[Route('/maintenance/add/{application}', name: 'app_add_maintenance')]
    public function index(Request $request, Application $application): Response
    {
        $maintenance = $this->maintenanceService->initMaintenance($application);
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->maintenanceService->createMaintenance($maintenance);
            $this->addFlash('success', 'Maintenance ajoutée avec succès');
            return $this->redirectToRoute('app_edit_application', ['id' => $application->getId()]);
        }

        return $this->render('maintenance_form\index.html.twig', [
            'maintenanceForm' => $form->createView()
        ]);
    }

    #[Route('/maintenance/edit/{id}', name: 'app_edit_maintenance')]
    public function edit(Request $request, int $id): Response
    {
        $maintenance = $this->maintenanceService->getMaintenanceById($id);
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->maintenanceService->updateMaintenance($maintenance);
            $this->addFlash('success', 'Modification effectuée avec succès');
            return $this->redirectToRoute('app_edit_application', ['id' => $maintenance->getApplication()->getId()]);
        }

        return $this->render('maintenance_form/index.html.twig', [
            'maintenanceForm' => $form->createView()
        ]);
    }

    #[Route('/maintenance/delete/{id}', name: 'app_delete_maintenance')]
    public function delete(Request $request, int $id): Response
    {
        $maintenance = $this->maintenanceService->getMaintenanceById($id);
        $appId = $maintenance->getApplication()->getId();
        if ($maintenance != null) {
            $this->maintenanceService->deleteMaintenance($maintenance);
            $this->addFlash('success', 'Maintenance supprimée avec succès');
        } else {
            $this->addFlash('error', 'Maintenance non trouvée');
        }

        return $this->redirectToRoute('app_edit_application', ['id' => $appId]);
    }
}