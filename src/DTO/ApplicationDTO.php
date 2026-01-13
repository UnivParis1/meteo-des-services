<?php

namespace App\DTO;

use League\Period\Period;
use League\Period\InvalidInterval;

class ApplicationDTO
{
    public int $id;

    public string $title;

    public string $state;

    public string $message;

    public ?\DateTimeInterface $lastUpdate;

    public bool $isInMaintenance = false;

    public ?MaintenanceDTO $nextMaintenance = null;

    public array $nextMaintenances = [];

    public array $lastMaintenances = [];

    public array $histories = [];

    public array $orderedHistosAndMtncs = [];

    public array $orderedHistoriqueMtncs = [];

    public array $disponibilites = [];

    public function __construct(int $id,
        string $title,
        string $state,
        string $message,
        ?\DateTimeInterface $lastUpdate)
    {
        $this->id = $id;
        $this->title = $title;
        $this->state = $state;
        $this->message = $message;
        $this->lastUpdate = $lastUpdate;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    /**
     * Set the value of lastUpdate.
     *
     * @return self
     */
    public function setLastUpdate($lastUpdate): static
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    public function getNextMaintenances(): array
    {
        return $this->nextMaintenances;
    }

    public function getNextMaintenance(): ?MaintenanceDTO
    {
        return $this->nextMaintenance;
    }

    public function isInMaintenance(): bool
    {
        return $this->isInMaintenance;
    }

    public function setIsInMaintenance(bool $isInMaintenance): void
    {
        $this->isInMaintenance = $isInMaintenance;
    }

    public function setNextMaintenance(MaintenanceDTO $nextMaintenance): void
    {
        $this->nextMaintenance = $nextMaintenance;
    }

    public function setNextMaintenances(array $nextMaintenances): void
    {
        $this->nextMaintenances = $nextMaintenances;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * Get the value of histories.
     */
    public function getHistories(): array
    {
        return $this->histories;
    }

    /**
     * Set the value of histories.
     *
     * @return self
     */
    public function setHistories($histories): static
    {
        $this->histories = $histories;

        return $this;
    }

    /**
     * Get the value of maintenances
     */
    public function getLastMaintenances()
    {
        return $this->lastMaintenances;
    }

    /**
     * Set the value of maintenances
     *
     * @return  self
     */
    public function setLastMaintenances($lastMaintenances)
    {
        $this->lastMaintenances = $lastMaintenances;

        return $this;
    }

    /**
     * Get the value of orderedHistosAndMtncs
     */
    public function getOrderedHistosAndMtncs()
    {
        return $this->orderedHistosAndMtncs;
    }

    /**
     * Set the value of orderedHistosAndMtncs
     *
     * @return  self
     */
    public function setOrderedHistosAndMtncs($orderedHistosAndMtncs)
    {
        $this->orderedHistosAndMtncs = $orderedHistosAndMtncs;

        return $this;
    }

    /**
     * Get the value of orderedHistoriqueMtncs
     */
    public function getOrderedHistoriqueMtncs()
    {
        return $this->orderedHistoriqueMtncs;
    }

    /**
     * Set the value of orderedHistoriqueMtncs
     *
     * @return  self
     */
    public function setOrderedHistoriqueMtncs($orderedHistoriqueMtncs)
    {
        $this->orderedHistoriqueMtncs = $orderedHistoriqueMtncs;

        return $this;
    }

    public static function createDisponibilite($orderedHistosAndMtncs, $appState, $isInMaintenance, $nextMaintenance): array
    {
        if (sizeof($orderedHistosAndMtncs) ==0)
            return [];

        $orderedHistosAndMtncs = array_reverse($orderedHistosAndMtncs);

        $genPeriods = [];
        if (sizeof($events = $orderedHistosAndMtncs) > 0) {
            $i = 0;
            $start = null;
            $end = null;
            do {
                $event = $events[$i];

                $isMtnc = str_contains(get_class($event), 'HistoryMaintenanceDTO');

                if ($start && !$end) {
                    if ($isMtnc) {
                        $genPeriods[] = ['etat' => $events[$lastAppIdx]->getState(), 'period' => Period::fromDate($start, $event->startingDate)];
                    } else {
                        $end = $event->getDate();

                        if ($start < $end) {
                            $genPeriods[] = ['etat' => $events[$lastAppIdx]->getState(), 'period' => Period::fromDate($start, $end )];
                            $lastAppIdx = $i;
                        }
                    }
                }

                if (!$isMtnc) { // alors c'est une application
                    $start = $event->getDate();
                    $lastAppIdx = $i;
                    $end = null;
                } else {
                    $start = $event->startingDate;
                    $end = $event->endingDate;
                    $genPeriods[] = ['etat' => $event->getState(), 'period' => Period::fromDate($start, $end)];
                    $start = $end;
                    $end = null;
                }

                $i++;
            } while ($i < sizeof($orderedHistosAndMtncs));
        }

        $lastGen = end($genPeriods)['period'];
        $state = $isInMaintenance ? $nextMaintenance->getState() : $appState;
        $genPeriods[] = ['etat' => $state, 'period' => Period::fromDate($lastGen->startDate, new \DateTime('now') )];

        return $genPeriods;
    }

    /**
     * Get the value of disponibilites
     */
    public function getDisponibilites(): array
    {
        return $this->disponibilites;
    }

    /**
     * Set the value of disponibilites
     *
     * @return  self
     */
    public function setDisponibilites($disponibilites): self
    {
        $this->disponibilites = $disponibilites;

        return $this;
    }
}
