<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Front\ApplicationsSorter;
use App\Service\ApplicationService;
use App\Model\IconsName;

#[Route('/api', name: 'api_')]
final class ApiController extends AbstractController
{
    public function __construct(
        public ApplicationService $applicationService,
        public ApplicationsSorter $applicationsSorter,
    ) {}

    #[Route('/', name: 'api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api', name: 'meteo', format: 'json')]
    public function api_index(Request $request): JsonResponse
    {
        $fname = $request->get('fname', '');
        $selectedState = $request->get('state', '');

        $applications = $this->applicationService->getApplicationByFilters($fname, $selectedState);
        $applications = $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);

        return new JsonResponse($applications);
    }

    #[Route('/application/{id}', name: 'application_details')]
    public function api_application_details(int $id): JsonResponse
    {
        $application = $this->applicationService->getApplicationById($id);

        $state = $application->getState();
        $applicationDTO = $this->applicationService->convertToDTO($application, null);

        if ($applicationDTO->isInMaintenance()) {
            $state = $applicationDTO->nextMaintenance->state;
        }

        // supprime l'historique de l'objet DTO si l'utilisateur n'est pas superviseur
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $applicationDTO->setHistories([]);
            $applicationDTO->setOrderedHistoriqueMtncs([]);
            $applicationDTO->setOrderedHistosAndMtncs([]);
        }

        $data = [
            'application' => $applicationDTO,
            'icone' => IconsName::$iconsName[$state],
            'icones' => IconsName::$iconsName
        ];

        return $this->json($data);
    }
}
