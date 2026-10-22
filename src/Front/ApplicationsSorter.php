<?php

namespace App\Front;

class ApplicationsSorter
{
    public function sortApplicationsByStateAndLastUpdate(array &$applications): array
    {
        for ($i = 0; $i < count($applications); ++$i) {
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

        // ajoute une comparaison pour avoir les applications qui seront en maintenance en premier
        usort(
            $applications,
            function ($app1, $app2): int {
                if (!($app1->getNextMaintenance() && $app2->getNextMaintenance())) {
                    return $app2->getNextMaintenance() <=> $app1->getNextMaintenance();
                }

                return $app1->getNextMaintenance()->getStartingDate() <=> $app2->getNextMaintenance()->getStartingDate();
            }
        );

        // fait ensuite une comparaison sur le statut (pour mettre les apps en maintenance en premier)
        usort(
            $applications,
            function ($app1, $app2): int {
                $state1 = $app1->getState();
                $state2 = $app2->getState();

                $ord1 = self::getOrderRankFromState($state1);
                $ord2 = self::getOrderRankFromState($state2);

                if ($ord1 === $ord2 && !($ord1 === 0 || $ord2 === 1)) {
                    $d1 = $app1->getLastupdate();
                    $d2 = $app2->getLastupdate();

                    return $d2 <=> $d1;
                }
                return $ord2 <=> $ord1;
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
