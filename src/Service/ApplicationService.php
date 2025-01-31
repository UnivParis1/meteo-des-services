<?php

namespace App\Service;

use App\DTO\ApplicationDTO;
use App\Entity\Application;
use App\Entity\ApplicationHistory;
use App\Repository\ApplicationHistoryRepository;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;

class ApplicationService
{

    public function __construct(public ApplicationRepository        $applicationRepository,
                                public ApplicationHistoryRepository $applicationHistoryRepository,
                                public MaintenanceService           $maintenanceService)
    {
    }

    public function createApplicationFromMeteoDesServices(Application $application): void
    {
        $application->setIsFromJson(false);
        $this->applicationRepository->createApplication($application);
        $this->updateHistory($application, "creation");
    }

    public function getAllApplications(): array
    {
        // Tableau sous format DTO
        $applications = new ArrayCollection();

        $allApplications = $this->applicationRepository->findAll();
        foreach ($allApplications as $application) {
            $applications->add($this->convertToDTO($application));
        }
        return $applications->toArray();
    }

    public function getApplicationNamesArrayForForm(): array
    {
        $applications = $this->getAllApplications();

        //récupération des noms
        $names = array_map(function ($application) {
            return $application->getTitle();
        }, $applications);

        //ordre alphabétique

        sort($names);

        //mapper clé valeur
        return array_combine($names, $names);
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

    private function getApplicationByFnameProperty(string $fname): Application|null
    {
        $application = $this->applicationRepository->findOneBy(['fname' => $fname]); // fname fait le lien entre JSon et BDD
        if ($application == null) {
            // Cas de JSon dynamique : Appli du JSon non référencée dans la BDD donc création d'une nouvelle appli
            $application = $this->applicationRepository->insertApplicationWithFname($fname);
            $this->updateHistory($application, "creation");
        } else if ($application->isIsArchived()) {
            return null;
        }
        return $application;
    }

    public function getApplicationById(int $id): Application
    {
        return $this->applicationRepository->findOneBy(['id' => $id]);
    }

    /*
     * Retourne les applications stockées en bdd
     */
    private function getApplicationsCreatedByMeteo(): ArrayCollection
    {
        $this->applicationRepository->findAll();
        $applications = $this->applicationRepository->findBy([
            'isFromJson' => false,
            'isArchived' => false]);
        return new ArrayCollection($applications);
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
        $history->setAuthor("ADMIN"); // TO CHANGE
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
