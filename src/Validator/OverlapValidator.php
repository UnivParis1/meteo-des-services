<?php

namespace App\Validator;

use App\Entity\Application;
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use League\Period\Period;

final class OverlapValidator extends ConstraintValidator
{
    public function validate(mixed $object, Constraint $constraint): void
    {
        /* @var Application $object*/
        if (! ($object instanceof Application) || null === $object || '' === $object)
            return;

        $context = $this->context;

        if (str_contains($context->getRoot()->getName(), 'edit'))
            return;

        /* @var Maintenance $mtncData  */
        $mtncAjout = $context->getObject();;
        $startingDate = $mtncAjout->getStartingDate();
        $endingDate = $mtncAjout->getEndingDate();

        if ($startingDate && $endingDate) {
            if ($endingDate <= $startingDate) {
                $context->buildViolation('La date de fin ne peut pas être antérieure ou égale à la date de début.' . ' TESTMSG 44242')
                    ->atPath('endingDate')
                    ->addViolation();
                return;
            }
        }

        $periodAjout = Period::fromDate($startingDate, $endingDate);

        $test = false;
        $existingMaintenances = $object->getMaintenances();
        foreach($existingMaintenances as $maintenance) {
            if ($maintenance->isIsArchived() || $maintenance->getEndingDate() < new DateTime())
                continue;

            $periodMaintenance = Period::fromDate($maintenance->getStartingDate(), $maintenance->getEndingDate());

            if ($periodMaintenance->overlaps($periodAjout) || $periodAjout->overlaps($periodMaintenance)) {
                $test = true;
                break;
            }
        }

        if (!$test)
            return;

        $context->buildViolation($constraint->message)->addViolation();
    }
}
