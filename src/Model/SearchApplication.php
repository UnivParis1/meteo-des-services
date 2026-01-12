<?php

namespace App\Model;

class SearchApplication
{
    public string $searchTerm = '';
    public string $selectedState = '';

    public ?int $limit = 30;

    public static $etats =  ['unavailable'=> 'Indisponible',
                             'perturbed'=> 'Perturbé',
                             'operational'=> 'Opérationnel',
                             'default'=> 'Non renseigné'];
}
