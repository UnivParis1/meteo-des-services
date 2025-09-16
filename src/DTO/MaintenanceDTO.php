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

        $adiff = ['jour' => $diff->format("%d"),'heure' => $diff->format("%h"),'minute' => $diff->format("%i") ];

        $avalues = array_values($adiff);
        $akeys = array_keys($adiff);
        $totaltime = "";

        for ($idx = 0; $idx < count($adiff); $idx++) {
            $duree = "{$akeys[$idx]}";
            $temps = intval($avalues[$idx]);

            if ($idx > 0 && strlen($totaltime) > 0)
                $totaltime .= " ";

            // test si duree est par exemple: 1 heure et 12 minutes
            if ($idx == 2 && $temps > 0 && $avalues[1] > 0)
                $totaltime .= " et ";

            switch ($temps) {
                case 0:
                    continue;
                case 1:
                    $totaltime .= "$temps $duree";
                    break;
                case $temps > 1:
                    $totaltime .= "$temps {$duree}s";
                    break;
            }
        }

        $this->totalTime = $totaltime;
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