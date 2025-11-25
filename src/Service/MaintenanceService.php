<?php

namespace App\Service;

use App\DTO\ApplicationDTO;
use App\DTO\MaintenanceDTO;
use App\Entity\Application;
use App\Entity\Maintenance;
use App\Entity\MaintenanceHistory;
use App\Repository\MaintenanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;

class MaintenanceService
{
    public function __construct(public MaintenanceRepository $maintenanceRepository,
        private Security $security)
    {
    }

    public function createMaintenance(Maintenance $maintenance): void
    {
        $this->maintenanceRepository->createMaintenance($maintenance);
        $this->updateHistory($maintenance, 'creation');
    }

    public function updateMaintenance(Maintenance $maintenance): void
    {
        $this->maintenanceRepository->updateMaintenance($maintenance);
        $this->updateHistory($maintenance, 'updating');
    }

    public function getMaintenanceById(int $id): Maintenance
    {
        return $this->maintenanceRepository->findOneBy(['id' => $id]);
    }

    public function setNextMaintenancesOfApplication(ApplicationDTO $application): ApplicationDTO
    {
        $now = new \DateTime();
        $maxNbOfMaintenances = 3;
        $nextMaintenances = $this->maintenanceRepository->findNextMaintenancesFromApplication($application->getId(), $maxNbOfMaintenances);
        if (0 === count($nextMaintenances)) {
            return $application;
        }

        $maintenances = [];
        $nextMaintenanceEstInsere = false;
        foreach ($nextMaintenances as $nextMaintenanceEntity) {
            $nextMaintenanceDTO = $this->convertToDTO($nextMaintenanceEntity);

            // ne met que la prochaine maintenance
            if (!$nextMaintenanceEstInsere) {
                $application->setNextMaintenance($nextMaintenanceDTO);
                $nextMaintenanceEstInsere = true;
            }

            if ($nextMaintenanceDTO->getStartingDate() < $now && $nextMaintenanceDTO->getEndingDate() > $now) {
                $application->setIsInMaintenance(true);
            }
            $maintenances[] = $nextMaintenanceDTO;
        }
        $application->setNextMaintenances($maintenances);

        return $application;
    }

    public function getNextMaintenancesFromApplication(int $applicationId): array
    {
        $entites = $this->maintenanceRepository->findNextMaintenancesFromApplication($applicationId);
        $dtos = new ArrayCollection();
        foreach ($entites as $entity) {
            $dtos->add($this->convertToDTO($entity));
        }

        return $dtos->toArray();
    }

    private function convertToDTO(Maintenance $maintenance): MaintenanceDTO
    {
        return new MaintenanceDTO(
            $maintenance->getId(),
            $maintenance->getApplicationState(),
            $maintenance->getStartingDate(),
            $maintenance->getEndingDate(),
            $maintenance->getMessage()
        );
    }

    private function updateHistory(Maintenance $maintenance, string $historyType): void
    {
        $history = new MaintenanceHistory();
        $history->setMaintenance($maintenance);
        $history->setStartingDate($maintenance->getStartingDate());
        $history->setEndingDate($maintenance->getEndingDate());
        $history->setApplicationState($maintenance->getApplicationState());
        $history->setType($historyType);
        // Date créée automatiquement par le constructeur
        $history->setAuthor($this->security->getUser()->getUserIdentifier());

        if (strlen($maintenance->getMessage()) > 0) {
            $history->setMessage($maintenance->getMessage());
        }

        $this->maintenanceRepository->createHistory($history);
    }

    public function initMaintenance(Application $application): Maintenance
    {
        $maintenance = new Maintenance();
        $maintenance->setApplication($application);
        $startingDate = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $startingDate->modify('+1 hour')->setTime($startingDate->format('H'), 0, 0);
        $maintenance->setStartingDate($startingDate);
        $endingDate = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $endingDate->modify('+2 hour')->setTime($endingDate->format('H'), 0, 0);
        $maintenance->setEndingDate($endingDate);

        return $maintenance;
    }

    public function deleteMaintenance(Maintenance $maintenance): void
    {
        $this->updateHistory($maintenance, 'deletion');
        $this->maintenanceRepository->deleteMaintenance($maintenance);
    }
}
