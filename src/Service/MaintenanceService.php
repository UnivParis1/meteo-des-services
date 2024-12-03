<?php

namespace App\Service;

use App\DTO\ApplicationDTO;
use App\DTO\MaintenanceDTO;
use App\Entity\Application;
use App\Entity\Maintenance;
use App\Entity\MaintenanceHistory;
use App\Repository\MaintenanceRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class MaintenanceService
{
    public function __construct(public MaintenanceRepository $maintenanceRepository)
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
        if (count($nextMaintenances) > 0) {
            $nextMaintenance = $this->convertToDTO($nextMaintenances[0]);
            $application->setNextMaintenance($nextMaintenance);
            if ($nextMaintenance->getStartingDate() < $now && $nextMaintenance->getEndingDate() > $now) {
                $application->setIsInMaintenance(true);
            }
            $maintenances = array($nextMaintenance);
            for ($i = 1; $i < count($nextMaintenances); $i++) {
                array_push($maintenances, $this->convertToDTO($nextMaintenances[$i]));
            }
            $application->setNextMaintenances($maintenances);
        }
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
            $maintenance->getEndingDate()
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
        $history->setAuthor("ADMIN"); // TO CHANGE
        $this->maintenanceRepository->createHistory($history);
    }

    public function initMaintenance(Application $application): Maintenance
    {
        $maintenance = new Maintenance();
        $maintenance->setApplication($application);
        $startingDate = new DateTime('now', new \DateTimeZone('Europe/Paris'));
        $startingDate->modify('+1 hour')->setTime($startingDate->format('H'), 0, 0);
        $maintenance->setStartingDate($startingDate);
        $endingDate = new DateTime('now', new \DateTimeZone('Europe/Paris'));
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