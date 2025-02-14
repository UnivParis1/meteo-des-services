<?php

namespace App\Front;

use App\DTO\ApplicationDTO;

class ApplicationsSorter
{

    public function sortApplicationsByStateAndLastUpdate(array $applications): array
    {
        usort($applications,
            function ($app1, $app2): int {
                return $this->compare($app1, $app2);
            });

        for ($i = 0; $i < count($applications); $i++) {
            $app = $applications[$i];

            if ($app->isInMaintenance()) {
                $app->setState($app->getNextMaintenance()->getState());
                $applications[$i] = $app;
            }
        }
        return $applications;
    }

    private function compare(ApplicationDTO $app1, ApplicationDTO $app2): int 
    {
        $state_app1 = $app1->getState();
        $state_app2 = $app2->getState();
        if ($state_app1 == $state_app2) {
            if ($app1->getNextMaintenance() == null
                && $app2->getNextMaintenance() == null) {
                // Critère 2 : modification la plus récente
                return ($app1->getLastUpdate() < $app2->getLastUpdate()) ? 1 : -1;
            } else {
                if ($app1->getNextMaintenance() == null) {
                    return 1;
                } elseif ($app2->getNextMaintenance() == null) {
                    return -1;
                } else {
                    // Critère 3 : maintenance la plus récente
                    return ($app1->getNextMaintenance()->getStartingDate() > $app2->getNextMaintenance()->getStartingDate()) ? 1 : -1;
                }
            }
        } else {
            // Critère 1 : tri en fonction des états
            return ($this->getOrderRankFromState($state_app1) < $this->getOrderRankFromState($state_app2)) ? 1 : -1;
        }
    }

    // Permet de donner un ordre d'importance aux états
    private function getOrderRankFromState(string $state): int
    {
        return match ($state) {
            'default' => 0,
            'operational' => 1,
            'perturbed' => 2,
            'unavailable' => 3,
        };
    }
}