<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options): void
    {
        if (isset($options['extra_options']['meteoAdminChoiceExtension']) && true == $options['extra_options']['meteoAdminChoiceExtension']) {
            $builder->addModelTransformer(new CallbackTransformer(
                fn ($rolesAsArray): ?string => count($rolesAsArray) ? implode(',', $rolesAsArray) : null,
                fn ($rolesAsString): array => explode(',', $rolesAsString)
            ));
        }
    }
}
