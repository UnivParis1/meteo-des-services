<?php

namespace App\Service;

use App\DTO\ApplicationDTO;
use App\Entity\Application;
use App\Entity\ApplicationHistory;
use App\Repository\ApplicationHistoryRepository;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;
class ApplicationService
{

    public function __construct(public  ApplicationRepository        $applicationRepository,
                                public  ApplicationHistoryRepository $applicationHistoryRepository,
                                public  MaintenanceService           $maintenanceService,
                                private Security                     $security
                                )
    {
    }

    public function createApplicationFromMeteoDesServices(Application $application): void
    {
        $this->applicationRepository->createApplication($application);
        $this->updateHistory($application, "creation");
    }

    public function getAllApplications(bool $archived = false): array
    {
        // Tableau sous format DTO
        $applications = new ArrayCollection();

        $allApplications = $archived ? $this->applicationRepository->findAll() : $this->applicationRepository->findAllNotArchived();
        foreach ($allApplications as $application) {
            $applications->add($this->convertToDTO($application, null));
        }
        return $applications->toArray();
    }

    public function getApplicationNamesArrayForForm(): array
    {
        $applications = $this->getAllApplications(archived: false);

        $array = ['' => ''];
        foreach ($applications as $application) {
            $title = $application->getTitle();
            $array[$title] = $title;
        }
        return $array;
    }

    public function convertToDTO(Application $application, ?string $title): ApplicationDTO
    {
        $dto = new ApplicationDTO(
            $application->getId(),
            $title == null ? $application->getTitle() : $title,
            $application->getState(),
            ($application->getMessage() == null ? "" : $application->getMessage()), // Handle null case
            $application->getLastUpdate()
        );
        // Ajout des maintenances
        $dto = $this->maintenanceService->setNextMaintenancesOfApplication($dto);
        return $dto;
    }

    public function getApplicationByFilters(string $searchTerm, string $stateFilter): array
    {
        $dtos = new ArrayCollection();
        foreach ($this->applicationRepository->findBySearchAndState($searchTerm, $stateFilter) as $application) {
            $dtos->add($this->convertToDTO($application, null));
        }
        return $dtos->toArray();
    }

    public function getApplicationById(int $id): Application
    {
        return $this->applicationRepository->findOneBy(['id' => $id]);
    }

    /*
     * historise une nouvelle modification (création, modif, supp) sur une app
     */
    private function updateHistory(Application $application, string $historyType): void
    {
        $history = new ApplicationHistory();
        $history->setApplication($application);
        $history->setState($application->getState());
        if ($application->getMessage() != null) {
            $history->setMessage($application->getMessage());
        }
        $history->setType($historyType);
        // Date créée automatiquement par le constructeur
        $history->setAuthor($this->security->getUser()->getUserIdentifier());
        $this->applicationRepository->createHistory($history);
    }

    public function updateApplication(Application $application): void
    {
        $this->applicationRepository->updateApplication($application);
        $this->updateHistory($application, 'updating');
    }

    public function deleteApplication(Application $application)
    {
        $this->updateHistory($application, 'deletion');
        $this->applicationRepository->deleteApplication($application);
    }
}
