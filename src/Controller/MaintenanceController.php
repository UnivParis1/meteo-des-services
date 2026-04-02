<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\MaintenanceType;
use App\Service\ApplicationService;
use App\Service\MaintenanceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/maintenance', name: 'maintenance_')]
class MaintenanceController extends AbstractController
{
    public function __construct(
        public ApplicationService $applicationService,
        public MaintenanceService $maintenanceService
    ) {}

    #[Route('/add/{application}', name: 'add')]
    public function index(Request $request, Application $application): Response
    {
        $maintenance = $this->maintenanceService->initMaintenance($application);
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->maintenanceService->createMaintenance($maintenance);
            $this->addFlash('success', 'Maintenance ajoutée avec succès');

            return $this->redirectToRoute('application_edit', ['id' => $application->getId()]);
        }

        return $this->render('maintenance_form\index.html.twig', [
            'maintenanceForm' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, int $id): Response
    {
        $maintenance = $this->maintenanceService->getMaintenanceById($id);
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->maintenanceService->updateMaintenance($maintenance);
            $this->addFlash('success', 'Modification effectuée avec succès');

            return $this->redirectToRoute('application_edit', ['id' => $maintenance->getApplication()->getId()]);
        }

        return $this->render('maintenance_form/index.html.twig', [
            'maintenanceForm' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, int $id): Response
    {
        $maintenance = $this->maintenanceService->getMaintenanceById($id);
        $appId = $maintenance->getApplication()->getId();
        if (null != $maintenance) {
            $this->maintenanceService->deleteMaintenance($maintenance);
            $this->addFlash('success', 'Maintenance supprimée avec succès');
        } else {
            $this->addFlash('error', 'Maintenance non trouvée');
        }

        return $this->redirectToRoute('application_edit', ['id' => $appId]);
    }
}
