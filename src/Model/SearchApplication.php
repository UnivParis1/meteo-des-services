<?php

namespace App\Model;

class SearchApplication
{
    public string $searchTerm = '';
    public string $selectedState = '';

    public ?int $limit = 30;
}