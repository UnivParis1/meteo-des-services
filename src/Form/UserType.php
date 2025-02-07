<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uuid', TextType::class)
            ->add('roles', TextType::class)
        ;

        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            fn ($rolesAsArray) => count($rolesAsArray) ? implode(',', $rolesAsArray): null,
            fn ($rolesAsString) => explode(',', $rolesAsString)
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
