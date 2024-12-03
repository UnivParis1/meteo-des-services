<?php

namespace App\DTO;

class MaintenanceDTO
{
    private int $id;

    private string $state;

    private \DateTimeInterface $startingDate;

    private \DateTimeInterface $endingDate;

    private string $totalTime;

    public function __construct(
        int                $id,
        string             $state,
        \DateTimeInterface $startingDate,
        \DateTimeInterface $endingDate
    )
    {
        $this->id = $id;
        $this->state = $state;
        $this->startingDate = $startingDate;
        $this->endingDate = $endingDate;

        $days = $startingDate->diff($endingDate)->days;
        if ($days > 1) {
            $this->totalTime = $days . " jours";
        } else if (($hours = $days * 24 + $startingDate->diff($endingDate)->h) <= 1) {
            $this->totalTime = $hours . " heure";
        } else {
            $this->totalTime = $hours . " heures";
        }
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
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartingDate(): \DateTimeInterface
    {
        return $this->startingDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndingDate(): \DateTimeInterface
    {
        return $this->endingDate;
    }

    /**
     * @return int
     */
    public function getTotalTime(): string
    {
        return $this->totalTime;
    }


}