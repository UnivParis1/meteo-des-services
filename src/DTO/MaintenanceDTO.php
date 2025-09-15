<?php

namespace App\DTO;

class MaintenanceDTO
{
    public int $id;

    public string $state;

    public \DateTimeInterface $startingDate;

    public \DateTimeInterface $endingDate;

    public string $totalTime;

    public ?string $message;

    public function __construct(
        int                $id,
        string             $state,
        \DateTimeInterface $startingDate,
        \DateTimeInterface $endingDate,
        ?string             $message
    )
    {
        $this->id = $id;
        $this->state = $state;
        $this->startingDate = $startingDate;
        $this->endingDate = $endingDate;
        $this->message = $message;

        $diff = $startingDate->diff($endingDate);
        $days = $diff->format("%d");

        if ($days > 1) {
            $this->totalTime = $days . " jours";
        } else if (($hours = $days * 24 + $startingDate->diff($endingDate)->h) <= 1) {
            $this->totalTime = $hours . " heure";
        } else {
            $this->totalTime = $hours . " heures";
        }

        $minutes = $diff->format("%i");

        if ($minutes > 0) {
            $this->totalTime .= " $minutes minute";

            if ($minutes > 1)
                $this->totalTime .= "s";
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

    public function getMessage(): ?string
    {
        return $this->message;
    }


}