<?php

namespace App\DTO;

use DateTimeInterface;

class HistoryMaintenanceDTO extends HistoryDTO
{
    public int $maintenance_id;

    public \DateTimeInterface $startingDate;
    public \DateTimeInterface $endingDate;

    public function __construct(int $id, int $maintenance_id, string $type, string $state, DateTimeInterface $date, string $author, ?string $message, \DateTimeInterface $startingDate, \DateTimeInterface $endingDate)
    {
        $this->maintenance_id = $maintenance_id;

        $this->startingDate = $startingDate;
        $this->endingDate = $endingDate;

        parent::__construct($id,  $type, $state, $date, $author, $message);
    }
}
