<?php

namespace App\Validator;

use App\Service\UserService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UidValidator extends ConstraintValidator
{
    public function __construct(private UserService $userService)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var Uid $constraint */

        $user = $this->userService->requestWS($value);

        if ($user !== null)
            return;

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation()
        ;
    }
}
