<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Form\UserType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ApplicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Application::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $array = parent::configureFields($pageName);

        $array[2] = ChoiceField::new('state')->setChoices([
            'default' => 'default',
            'operational' => 'operational',
            'perturbed' => 'perturbed',
            'unavailable' => 'unavailable',
        ]);

        $array[5] = ChoiceField::new('Categorie')->setChoices([
            "Communication" => "Communication",
            "Document" => "Document",
            "Vie administrative" => "Vie administrative",
            "Scolarité" => "Scolarité",
            "Collaboration" => "Collaboration",
            "Documentation" => "Documentation",
            "Gestion" => "Gestion",
            "Identité numérique" => "Identité numérique",
            "Administrateurs" => "Administrateurs",
            "Assistance" => "Assistance",
            "Usages" => "Usages",
            "ESUP-Portail" => "ESUP-Portail",
            "Renater" => "Renater"
        ]);

        $array[] = AssociationField::new('users');

        $array[] = ChoiceField::new('roles')->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", true)
                                       ->setFormTypeOption('mapped', true)
                                       ->setChoices(UserType::$choix);
        return $array;
    }
}
