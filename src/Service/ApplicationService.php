<?php

namespace App\Service;

use App\DTO\ApplicationDTO;
use App\DTO\HistoryDTO;
use App\DTO\MaintenanceDTO;
use App\Entity\Application;
use App\Entity\ApplicationHistory;
use App\Repository\ApplicationHistoryRepository;
use App\Repository\ApplicationRepository;
use App\Repository\TagsRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;

class ApplicationService
{

    public function __construct(
        public  ApplicationRepository        $applicationRepository,
        public  TagsRepository               $tagsRepository,
        public  ApplicationHistoryRepository $applicationHistoryRepository,
        public  MaintenanceService           $maintenanceService,
        private UserRepository               $userRepository,
        private Security                     $security
    ) {}

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

    private static function sortDateHistoriesDTO($histories)
    {
        $values = $histories;
        usort($values, static function (HistoryDTO $a, HistoryDTO $b): int {
            if ($a->getDate()->getTimestamp() === $b->getDate()->getTimestamp()) {
                return 0;
            }

            return $a->getDate()->getTimestamp() > $b->getDate()->getTimestamp() ? -1 : 1;
        });

        return $values;
    }
    public function convertToDTO(Application $application, ?string $title, bool $setHistory = false, $addMaintenancesToHistories = false): ApplicationDTO
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

        if ($setHistory) {
            $histories = $application->getHistories();

            $dtoHistories = [];
            foreach ($histories as $history) {
                $dtoHistories[] = new HistoryDTO(
                    $history->getId(),
                    $application->getId(),
                    $history->getType(),
                    $history->getState(),
                    $history->getDate(),
                    $this->userRepository->findOneByUid($history->getAuthor())->getDisplayName(),
                    $history->getMessage(),
                    false
                );
            }
            if (! $addMaintenancesToHistories) {
                $dto->setHistories(self::sortDateHistoriesDTO($dtoHistories));
            }
        }

        if ($addMaintenancesToHistories) {
            $maintenances = $application->getMaintenances();

            foreach ($maintenances as $maintenance) {
                $maintenanceHistories = $maintenance->getMaintenanceHistories();

                if (count($maintenanceHistories) > 0) {
                    $lastIdMaintenance = $maintenanceHistories->get(0);
                    foreach ($maintenanceHistories as $maintenanceHistory) {
                        if ($maintenanceHistory->getId() > $lastIdMaintenance->getId())
                            $lastIdMaintenance = $maintenanceHistory;
                    }

                    $dtoHistories[] = new HistoryDTO(
                        $lastIdMaintenance->getId(),
                        $lastIdMaintenance->getMaintenance()->getId(),
                        $lastIdMaintenance->getType(),
                        $lastIdMaintenance->getApplicationState(),
                        $lastIdMaintenance->getStartingDate(),
                        $this->userRepository->findOneByUid($lastIdMaintenance->getAuthor())->getDisplayName(),
                        $lastIdMaintenance->getMessage(),
                        true
                    );
                }
            }
            $dto->setHistories(self::sortDateHistoriesDTO($dtoHistories));
        }
        return $dto;
    }

    public function getApplicationByTag(string $searchTerm): array
    {
        $dtos = new ArrayCollection();
        $tags = $this->tagsRepository->findOneBy(['name' => $searchTerm]);

        if ($tags) {
            foreach ($this->applicationRepository->findAllByTags($tags) as $application) {
                if ($this->security->isGranted(current($application->getRoles()))) {
                    $dtos->add($this->convertToDTO($application, null));
                }
            }
        }
        return $dtos->toArray();
    }
    public function getApplicationByFilters(string $searchTerm, string $stateFilter): array
    {
        $dtos = new ArrayCollection();
        foreach ($this->applicationRepository->findBySearchAndState($searchTerm, $stateFilter) as $application) {
            // vérifie que les droit de l'utilisateur pour chaque programmes
            if ($this->security->isGranted(current($application->getRoles()))) {
                $dtos->add($this->convertToDTO($application, null));
            }
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
