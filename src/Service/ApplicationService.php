<?php

namespace App\Service;

use App\DTO\ApplicationDTO;
use App\DTO\HistoryApplicationDTO;
use App\DTO\HistoryMaintenanceDTO;
use App\DTO\HistoryDTO;
use App\DTO\MaintenanceDTO;
use App\Entity\Application;
use App\Entity\ApplicationHistory;
use App\Repository\ApplicationHistoryRepository;
use App\Repository\ApplicationRepository;
use App\Repository\TagsRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;

class ApplicationService
{
    public function __construct(
        public ApplicationRepository $applicationRepository,
        public TagsRepository $tagsRepository,
        public ApplicationHistoryRepository $applicationHistoryRepository,
        public MaintenanceService $maintenanceService,
        private UserRepository $userRepository,
        private Security $security,
    ) {
    }

    public function createApplicationFromMeteoDesServices(Application $application): void
    {
        $this->applicationRepository->createApplication($application);
        $this->updateHistory($application, 'creation');
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

    private static function sortDateHistoriesDTO(&$histories)
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

    /**
     * sortHistosMntcs
     *
     * ajoute les maintenances sur un tableau commun historique application et maintenances (pour permettre de réaliser des tris sur les dates)
     *
     * @param  array $histories
     * @param  array $lastMaintenances
     * @return array
     */
    private static function sortHistosMntcs(array $histories, array $lastMaintenances): array {

        foreach($lastMaintenances as $mtnc) {
            $histos = $mtnc->getHistories();
            $date = null;

            for ($i = 0; $i < count($histos); $i++) {
                $histo = $histos[$i];

                if ($i == 0) {
                    $date = $histo->getDate();
                    $ref = $histo;
                    continue;
                }

                if ($date < $histo->getDate()) {
                    $date = $histo->getDate();
                    $ref = $histo;
                }
            }

            $histories[] = $ref;
        }

        return self::sortDateHistoriesDTO($histories);
    }

    public function convertToDTO(Application $application, ?string $title, bool $setHistory = true, $addMaintenancesToHistories = true, $genereDisponibilite = true): ApplicationDTO
    {
        $appLastUpdate = $application->getLastUpdate();

        $dto = new ApplicationDTO(
            $application->getId(),
            null == $title ? $application->getTitle() : $title,
            $application->getState(),
            null == $application->getMessage() ? '' : $application->getMessage(), // Handle null case
            $appLastUpdate
        );
        // Ajout des maintenances
        $dto = $this->maintenanceService->setNextMaintenancesOfApplication($dto);

        if ($setHistory) {
            $histories = $application->getHistories();

            $dtoHistories = [];
            foreach ($histories as $history) {
                $dtoHistories[] = new HistoryApplicationDTO(
                    $history->getId(),
                    $application->getId(),
                    $history->getType(),
                    $history->getState(),
                    $history->getDate(),
                    $this->userRepository->findOneByUid($history->getAuthor())->getDisplayName(),
                    $history->getMessage(),
                );
            }
            $dto->setHistories(self::sortDateHistoriesDTO($dtoHistories));
        }

        if ($addMaintenancesToHistories) {

            $nextMaintenances = $dto->getNextMaintenances();

            $maintenances = $application->getMaintenances();
            $maintenancesDTO = [];
            $allHistoriqueMtncs = [];

            foreach ($maintenances as $maintenance) {
                // ne met pas les prochaines maintenances
                $isLast = true;
                foreach ($nextMaintenances as $nextMaintenance)
                    if ($maintenance->getId() == $nextMaintenance->getId())
                        $isLast = false;

                if ( ! $isLast)
                    continue;

                $maintenanceDTO = new MaintenanceDTO($maintenance->getId(), $maintenance->getApplicationState(), $maintenance->getStartingDate(), $maintenance->getEndingDate(), $maintenance->getMessage());

                $maintenanceHistories = $maintenance->getMaintenanceHistories();

                if (count($maintenanceHistories) > 0) {
                    $dtoMntcHistories = [];

                    foreach ($maintenanceHistories as $maintenanceHistory) {
                        $historyMaintenanceDTO = new HistoryMaintenanceDTO(
                            $maintenanceHistory->getId(),
                            $maintenanceHistory->getMaintenance()->getId(),
                            $maintenanceHistory->getType(),
                            $maintenanceHistory->getApplicationState(),
                            $maintenanceHistory->getDate(),
                            $this->userRepository->findOneByUid($maintenanceHistory->getAuthor())->getDisplayName(),
                            $maintenanceHistory->getMessage(),
                            $maintenanceHistory->getStartingDate(),
                            $maintenanceHistory->getEndingDate() );

                        $dtoMntcHistories[] =  $historyMaintenanceDTO;
                        $allHistoriqueMtncs[] = $historyMaintenanceDTO;
                    }
                    $maintenanceDTO->setHistories(self::sortDateHistoriesDTO($dtoMntcHistories));
                }
                $maintenancesDTO[] = $maintenanceDTO;
            }

            $dto->setLastMaintenances($maintenancesDTO);
            $dto->setOrderedHistoriqueMtncs(self::sortDateHistoriesDTO($allHistoriqueMtncs));

            $ordered = self::sortHistosMntcs($dto->getHistories(), $maintenancesDTO);
            $dto->setOrderedHistosAndMtncs($ordered);

            foreach ($ordered as $dtoHistory) {
                $lastDateMtnc = $dtoHistory->getDate();
                if ($lastDateMtnc > $appLastUpdate) {
                    $appLastUpdate = $lastDateMtnc;
                }
            }

            if ($dto->isInMaintenance()) {
                $mtncLastUpdate = $dto->getNextMaintenance()->getStartingDate();
            } else {
                $mtncLastUpdate = $dto->getLastUpdate();
            }

            $lastUpdate = ($appLastUpdate > $mtncLastUpdate) ? $appLastUpdate : $mtncLastUpdate;

            $dto->setLastUpdate($lastUpdate);
        }

        if ($genereDisponibilite) {
            ApplicationDTO::createDisponibilite($ordered);
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
        if (null != $application->getMessage()) {
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
