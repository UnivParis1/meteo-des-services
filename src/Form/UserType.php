<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public static array $choix = ["student" => "ROLE_STUDENT", "teacher" => "ROLE_TEACHER", "staff" => 'ROLE_STAFF', "admin" => 'ROLE_ADMIN', 'super_admin' => "ROLE_SUPER_ADMIN"];
    public static array $easyAdminEduPrincipalAffiliationDisplay = ["student" => "student", "teacher" => "teacher", "staff" => 'staff'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uid', TextType::class)
            ->add('roles', ChoiceType::class, [
               'required' => true,
               'choices' => self::$choix
            ]);

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
