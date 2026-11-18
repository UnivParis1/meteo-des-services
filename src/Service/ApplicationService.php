<?php

namespace App\Service;

use App\DTO\ApplicationDTO;
use App\DTO\HistoryApplicationDTO;
use App\DTO\HistoryMaintenanceDTO;
use App\DTO\HistoryDTO;
use App\DTO\MaintenanceDTO;
use App\Model\SearchApplication;
use App\Entity\Application;
use App\Entity\ApplicationHistory;
use App\Front\ApplicationsSorter;
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
        private ApplicationsSorter $applicationsSorter,
        private Security $security,
    ) {
    }

    /**
     * getApplicationsSearch
     *
     * agrège les résultats de recherche par filtres ainsi que par tag (s'il y'en a)
     * en utilisant l'objet SearchApplication ayant les paramètres de la recherche
     *
     * @param SearchApplication $searchApplication
     * @return Application[]
     */
    public function getApplicationsSearch(SearchApplication $searchApplication): array {
         $applications = [...$this->getApplicationByTag($searchApplication),
                          ...$this->getApplicationByFilters($searchApplication)];

        $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);

        return $applications;
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

    public function convertToDTO(Application $application, ?string $title, bool $setHistory = false, $addMaintenancesToHistories = true): ApplicationDTO
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

        if ($setHistory || $this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $histories = $application->getHistories();

            $dtoHistories = [];
            foreach ($histories as $history) {
                $author = $history->getAuthor();
                $user = $this->userRepository->findOneByUid($author);

                $displayName = $user ? ($user->getDisplayName() ?? $user->getUid()) : $author;

                $dtoHistories[] = new HistoryApplicationDTO(
                    $history->getId(),
                    $application->getId(),
                    $history->getType(),
                    $history->getState(),
                    $history->getDate(),
                    $displayName,
                    $history->getMessage(),
                );
            }
            $dto->setHistories(self::sortDateHistoriesDTO($dtoHistories));
        }

        if ($addMaintenancesToHistories || $this->security->isGranted('ROLE_SUPER_ADMIN')) {

            $nextMaintenances = $dto->getNextMaintenances();

            $maintenances = $application->getMaintenances();
            $maintenancesDTO = [];
            $allHistoriqueMtncs = [];

            foreach ($maintenances as $maintenance) {
                // ne met pas les prochaines maintenances
                $isLast = false;
                foreach ($nextMaintenances as $nextMaintenance)
                    if ($maintenance->getId() == $nextMaintenance->getId())
                        $isLast = true;

                if ( ! $isLast)
                    continue;

                $maintenanceDTO = new MaintenanceDTO($maintenance->getId(), $maintenance->getApplicationState(), $maintenance->getStartingDate(), $maintenance->getEndingDate(), $maintenance->getMessage());

                $maintenanceHistories = $maintenance->getMaintenanceHistories();

                if (count($maintenanceHistories) > 0) {
                    $dtoMntcHistories = [];

                    foreach ($maintenanceHistories as $maintenanceHistory) {
                        $author = $maintenanceHistory->getAuthor();
                        $user = $this->userRepository->findOneByUid($author);

                        $displayName = $user ? ($user->getDisplayName() ?? $user->getUid()) : $author;

                        $historyMaintenanceDTO = new HistoryMaintenanceDTO(
                            $maintenanceHistory->getId(),
                            $maintenanceHistory->getMaintenance()->getId(),
                            $maintenanceHistory->getType(),
                            $maintenanceHistory->getApplicationState(),
                            $maintenanceHistory->getDate(),
                            $displayName,
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

        return $dto;
    }

    public function getApplicationByTag(SearchApplication $searchApplication): array
    {
        $searchTerm = $searchApplication->searchTerm;
        $selectedState = $searchApplication->selectedState;

        $dtos = new ArrayCollection();
        $tag = $this->tagsRepository->findOneBy(['name' => $searchTerm]);

        if ($tag) {
            $applications = $this->applicationRepository->findByTagsAndState(tag: $tag, stateFilter: $selectedState);

            foreach ($applications as $application)
                if ($this->security->isGranted(current($application->getRoles())))
                    $dtos->add($this->convertToDTO($application, null));
        }
        return $dtos->toArray();
    }

    public function getApplicationByFilters(SearchApplication $searchApplication): array
    {
        $searchTerm= $searchApplication->searchTerm;
        $selectedState = $searchApplication->selectedState;

        $dtos = new ArrayCollection();
        foreach ($this->applicationRepository->findBySearchAndState($searchTerm, $selectedState) as $application) {
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

    public function updateHistoryObj(Application $application, string $historyType): ApplicationHistory
    {
        $history = new ApplicationHistory();
        $history->setApplication($application);
        $history->setState($application->getState());
        if (null != $application->getMessage()) {
            $history->setMessage($application->getMessage());
        }
        $history->setType($historyType);

        return $history;
    }

    /*
     * historise une nouvelle modification (création, modif, supp) sur une app
     */
    public function updateHistory(Application $application, string $historyType, $uid = null): void
    {
        $history = $this->updateHistoryObj($application, $historyType);
        // Date créée automatiquement par le constructeur
        $uid ??= $this->security->getUser()->getUserIdentifier();
        $history->setAuthor($uid);
        $this->applicationRepository->createHistory($history);
    }

    public function createApplication(Application $application, $uid = null): void
    {
        $this->applicationRepository->createApplication($application);
        $this->updateHistory($application, 'creation', $uid);
    }

    public function updateApplication(Application $application, $uid = null): void
    {
        $this->applicationRepository->updateApplication($application);
        $this->updateHistory($application, 'updating', $uid);
    }

    public function deleteApplication(Application $application, $uid = null)
    {
        $this->updateHistory($application, 'deletion', $uid);
        $this->applicationRepository->deleteApplication($application);
    }
}
