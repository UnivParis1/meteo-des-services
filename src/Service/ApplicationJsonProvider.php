<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;

class ApplicationJsonProvider
{

    // Renvoie des tableaux d'application de deux valeurs : fname et title issues du fichier json
    public function getApplicationInfosFromJsonFile(): ArrayCollection
    {
        // Chargement du fichier json
        $json = file_get_contents('json/applications.json');
        $data = json_decode($json, true);
        $applications = new ArrayCollection();
        // Parcours du fichier
        foreach ($data['layout']['folders'] as $folder) {
            foreach ($folder['portlets'] as $portlet) {
                // Ajoute les propriétés fname et title
                $applications->add(array($portlet['fname'], $portlet['title']));
            }
        }
        return $applications;
    }
}
