<?php

namespace App\DTO;

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
}
