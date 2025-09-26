<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Model\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ApplicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Application::class;
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
                 ->update(Crud::PAGE_INDEX, Action::NEW,
                    fn (Action $action): Action => $action->setLabel("Créer une application")
                 )->update(Crud::PAGE_INDEX, Action::EDIT,
                    fn (Action $action): Action => $action->setLabel("Editer l'application")
                 )->update(Crud::PAGE_INDEX, Action::DELETE,
                    fn (Action $action): Action => $action->setLabel("Supprimer l'application")
                 )->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN,
                    fn (Action $action): Action => $action->setLabel("Sauvegarder l'application")
                 )->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE,
                    fn (Action $action): Action => $action->setLabel("Sauvegarder et continuer l'édition")
                 )->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                    fn (Action $action): Action => $action->setLabel("Créer l'application")
                 )->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER,
                    fn (Action $action): Action => $action->setLabel("Créer puis créer une autre application")
                 );
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);

        return $crud
                 ->setPageTitle("index", "Listing des applications")
                 ->setPageTitle("new", "Création d'une application")
                 ->setPageTitle("edit", "Edition d'une application")
                 ->setEntityLabelInSingular("Application")
                 ->setEntityLabelInPlural("Applications");
    }

    public function configureFields(string $pageName): iterable
    {
         yield NumberField::new('id')->hideOnIndex()->hideOnForm();

        /** @var \EasyCorp\Bundle\EasyAdminBundle\Field\Field */
        yield TextField::new('title')->setLabel("Titre de l'application")
              ->setRequired(true);

        yield ChoiceField::new('state')
                                  ->setChoices([
                                    'default' => 'default',
                                    'operational' => 'operational',
                                    'perturbed' => 'perturbed',
                                    'unavailable' => 'unavailable',
                                  ])
                                  ->setLabel("Statut courant");

        /** @var \EasyCorp\Bundle\EasyAdminBundle\Field\Field */
        yield BooleanField::new('isArchived')->setLabel("Est archivée");

        yield TextField::new('description');

        yield ChoiceField::new('Categorie')->setChoices([
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

        yield BooleanField::new('isFromJson')->setLabel("source JSON")
               ->hideOnIndex()
               ->setHelp("Provenance de la donnée depuis le JSON des applications ENT");

        yield AssociationField::new('users')
                                       ->hideOnIndex()
                                       ->hideOnDetail()
                                       ->hideWhenCreating()
                                       ->hideWhenUpdating();

        yield ChoiceField::new('roles')->setLabel("Autorisation d'accès")
                                       ->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", false)
                                       ->setFormTypeOption('mapped', true)
                                       ->setFormTypeOption('extra_options', ['meteoAdminChoiceExtension' => true]) // identique à UserCrudController
                                       ->setChoices(UserRoles::$choix)
                                       ->setHelp("définis les autorisations d'accès à l'application de manière hiérarchique (ex: un enseignant a accès aux applications étudiantes, un biatssp aux applications enseignantes...)");

        yield AssociationField::new('tags')
                                           ->hideOnIndex()
                                           ->setFormTypeOption('disabled', 'disabled');
    }
}
