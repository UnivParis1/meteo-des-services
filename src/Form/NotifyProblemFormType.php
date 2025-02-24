<?php

namespace App\Form;

use App\Model\NotifyProblem;
use App\Service\ApplicationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotifyProblemFormType extends AbstractType
{
    public function __construct(public ApplicationService $applicationService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', ChoiceType::class,
                ['required' => true,
                'choices' =>  $this->applicationService->getApplicationNamesArrayForForm(),
                'autocomplete' => true,
                'empty_data' => '',
                'attr' => [
                    'data-controller' => 'custom-autocomplete',
                    ],
                ])
            ->add('message', TextareaType::class, ['required' => true, 'empty_data' => '']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NotifyProblem::class,
            'attr' => [
                'data-controller' => 'custom-autocomplete',
            ]
        ]);
    }
}