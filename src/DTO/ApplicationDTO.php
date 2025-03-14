<?php

namespace App\DTO;

class ApplicationDTO
{
    private int $id;

    private string $title;

    private string $state;

    private string $message;

    private ?\DateTimeInterface $lastUpdate;

    private bool $isInMaintenance = false;

    private ?MaintenanceDTO $nextMaintenance = null;

    private array $nextMaintenances = array();

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


}