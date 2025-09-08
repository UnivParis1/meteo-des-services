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
        $array = parent::configureFields($pageName);

        /** @var \EasyCorp\Bundle\EasyAdminBundle\Field\Field */
        $id = $array[0];
        $id->hideOnIndex();

        /** @var \EasyCorp\Bundle\EasyAdminBundle\Field\Field */
        $title = $array[1];
        $title->setLabel("Titre de l'application");

        $array[2] = ChoiceField::new('state')
                                  ->setChoices([
                                    'default' => 'default',
                                    'operational' => 'operational',
                                    'perturbed' => 'perturbed',
                                    'unavailable' => 'unavailable',
                                  ])
                                  ->setLabel("Statut courant");

        /** @var \EasyCorp\Bundle\EasyAdminBundle\Field\Field */
        $isArchived = $array[3];
        $isArchived->setLabel("Est archivée");

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

        /** @var \EasyCorp\Bundle\EasyAdminBundle\Field\Field */
        $isJson = $array[6];
        $isJson->setLabel("source JSON")
               ->hideOnIndex()
               ->setHelp("Provenance de la donnée depuis le JSON des applications ENT");

        $array[] = AssociationField::new('users')
                                       ->hideOnIndex()
                                       ->hideOnDetail()
                                       ->hideWhenCreating()
                                       ->hideWhenUpdating();

        $array[] = ChoiceField::new('roles')->setLabel("Niveau ACL")
                                       ->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", false)
                                       ->setFormTypeOption('mapped', true)
                                       ->setFormTypeOption('extra_options', ['meteoAdminChoiceExtension' => true]) // identique à UserCrudController
                                       ->setChoices(UserRoles::$choix);

        $array[] = AssociationField::new('tags');
        return $array;
    }
}
