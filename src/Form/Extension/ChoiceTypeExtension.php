<?php

namespace App\Form\Extension;

use App\Model\UserRoles;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options): void
    {
        if (isset($options['extra_options']['meteoAdminChoiceExtension']) && $options['extra_options']['meteoAdminChoiceExtension'] == true) {
            $builder->addModelTransformer(new CallbackTransformer(
                fn ($rolesAsArray):?string => count($rolesAsArray) ? implode(',', $rolesAsArray): null,
                fn ($rolesAsString):array => explode(',', $rolesAsString)
            ));
        }
    }
}