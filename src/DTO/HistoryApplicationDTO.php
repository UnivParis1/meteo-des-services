<?php

namespace App\DTO;

use DateTimeInterface;

class HistoryApplicationDTO extends HistoryDTO
{
    public int $application_id;

    public function __construct(int $id, int $application_id, string $type, string $state, DateTimeInterface $date, string $author, ?string $message)
    {
        $this->application_id = $application_id;

        parent::__construct($id, $type, $state, $date, $author, $message);
    }
}
