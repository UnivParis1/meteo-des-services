<?php

namespace App\DTO;

use App\Entity\ApplicationHistory;
use Doctrine\Common\Collections\Collection;

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

    public array $histories = [];

    public function __construct(int                $id,
                                string             $title,
                                string             $state,
                                string             $message,
                                ?\DateTimeInterface $lastUpdate)
    {
        $this->id = $id;
        $this->title = $title;
        $this->state = $state;
        $this->message = $message;
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string
     */
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
     * Set the value of lastUpdate
     *
     * @return  self
     */
    public function setLastUpdate($lastUpdate): static
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * @return array
     */
    public function getNextMaintenances(): array
    {
        return $this->nextMaintenances;
    }

    public function getNextMaintenance(): MaintenanceDTO|null
    {
        return $this->nextMaintenance;
    }

    public function isInMaintenance(): bool
    {
        return $this->isInMaintenance;
    }

    /**
     * @param bool $isInMaintenance
     */
    public function setIsInMaintenance(bool $isInMaintenance): void
    {
        $this->isInMaintenance = $isInMaintenance;
    }

    /**
     * @param MaintenanceDTO $nextMaintenance
     */
    public function setNextMaintenance(MaintenanceDTO $nextMaintenance): void
    {
        $this->nextMaintenance = $nextMaintenance;
    }

    /**
     * @param array $nextMaintenances
     */
    public function setNextMaintenances(array $nextMaintenances): void
    {
        $this->nextMaintenances = $nextMaintenances;
    }

    /**
     * @param  string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * Get the value of histories
     */
    public function getHistories(): array
    {
        return $this->histories;
    }

    /**
     * Set the value of histories
     *
     * @return  self
     */
    public function setHistories($histories): static
    {
        $this->histories = $histories;

        return $this;
    }
}