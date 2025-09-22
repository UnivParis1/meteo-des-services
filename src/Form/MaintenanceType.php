<?php

namespace App\Form;

use DateTime;
use App\Entity\Application;
use App\Entity\Maintenance;
use DateTimeInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

// Pour importer toutes les contraintes sous le namespace Assert

class MaintenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('application', EntityType::class, [
                'class' => Application::class,
                'choice_label' => 'title',
                'required' => true
            ])
            ->add('applicationState', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Indisponible' => 'unavailable',
                    'Perturbé' => 'perturbed',
                    'Opérationnel' => 'operational'
                ]])
            ->add('startingDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual([
                        'value' => new \DateTime('today'),
                        'message' => 'La date de début ne peut pas être antérieure à aujourd\'hui.',
                    ])
                ],
                'attr' => [
                    'min' => (new \DateTime('now'))->format('d/m/Y H:i')
                ]
            ])
            ->add('endingDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Callback([$this, 'validateDates']),
                ]
            ])
            ->add('message', TextareaType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Maintenance::class,
        ]);
    }

    public function validateDates($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $startingDate = $form->get('startingDate')->getData();
        $endingDate = $value;

        if ($startingDate && $endingDate) {
            if ($endingDate < $startingDate) {
                $context->buildViolation('La date de fin ne peut pas être antérieure à la date de début.')
                    ->atPath('endingDate')
                    ->addViolation();
            }
        }
    }
}
