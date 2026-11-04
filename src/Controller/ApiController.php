<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ApplicationService;
use App\Model\IconsName;
use App\Model\SearchApplication;
use App\Repository\ApplicationRepository;

#[Route('/api', name: 'api_')]
final class ApiController extends AbstractController
{
    public function __construct(
        private ApplicationService $applicationService,
        private ApplicationRepository $applicationRepository,
    ) {}

    #[Route('/', name: 'meteo', format: 'json')]
    public function api_index(): JsonResponse
    {
        $searchApplication = new SearchApplication();

        $applications = $this->applicationService->getApplicationsSearch($searchApplication);

        return new JsonResponse($applications);
    }

    #[Route('/stats', name: 'stats', format: 'json')]
    public function api_stats(): JsonResponse
    {
        $etats = SearchApplication::$etats;
        $etatkeys = array_keys($etats);
        // enleve Non renseigné
        $dispos = array_slice($etatkeys, 0,  -1);

        $json = [];
        foreach ($dispos as $dispo) {
            $apps = $this->applicationRepository->findBySearchAndState('',$dispo);
            $json[$dispo] = count($apps);
        }
        return new JsonResponse($json);
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
