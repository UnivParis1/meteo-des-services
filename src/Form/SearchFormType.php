<?php

namespace App\Form;

use App\Model\SearchApplication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceValue;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('searchTerm', TextType::class, [
                'attr' => [
                    'placeholder' => 'Rechercher...'
                ],
                'required' => false,
                'empty_data' => ''
            ])
            ->add('selectedState', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Tous les états' => '',
                    'Indisponible' => 'unavailable',
                    'Perturbé' => 'perturbed',
                    'Opérationnel' => 'Operational',
                    'Non renseigné' => 'default'
                ]
            ])->add('limit', ChoiceType::class, [
                'required' => true,
                'choices' => ['Tous' => null,'10'=> 10,'20' => 20, '30' => 30]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchApplication::class,
        ]);
    }
}
