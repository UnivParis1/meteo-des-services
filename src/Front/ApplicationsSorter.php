<?php

namespace App\Front;

use App\DTO\ApplicationDTO;

class ApplicationsSorter
{

    public function sortApplicationsByStateAndLastUpdate(array $applications): array
    {
        for ($i = 0; $i < count($applications); $i++) {
            $app = $applications[$i];

            if ($app->isInMaintenance()) {
                $app->setState($app->getNextMaintenance()->getState());
                $applications[$i] = $app;
            }
        }

        // fait d'abord une comparaison string converti en ASCII puis mis en majuscule
        usort(
            $applications,
            fn ($app1, $app2): int => strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $app1->getTitle())) <=> strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $app2->getTitle()))
        );

        // fait ensuite une comparaison sur le statut (pour mettre les apps en maintenance en premier)
        usort(
            $applications,
            fn ($app1, $app2): int => self::getOrderRankFromState($app2->getState()) <=> self::getOrderRankFromState($app1->getState())
        );

        // ajoute une comparaison pour avoir les applications qui seront en maintenance en premier
        usort(
            $applications,
            function ($app1, $app2): int {
                if (! ($app1->getNextMaintenance() &&  $app2->getNextMaintenance()))
                    return $app2->getNextMaintenance() <=> $app1->getNextMaintenance();

                return $app1->getNextMaintenance()->getStartingDate() <=> $app2->getNextMaintenance()->getStartingDate();
            }
        );
        return $applications;
    }
    private static function getOrderRankFromState(string $state): int
    {
        return match ($state) {
            'default' => 0,
            'operational' => 1,
            'perturbed' => 2,
            'unavailable' => 3,
        };
    }
}