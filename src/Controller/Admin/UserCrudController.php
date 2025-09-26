<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Model\UserRoles;
use App\Form\RolesType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

#[IsGranted('ROLE_SUPER_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
                 ->update(Crud::PAGE_INDEX, Action::NEW,
                    fn (Action $action): Action => $action->setLabel("Créer un utilisateur")
                 )->update(Crud::PAGE_INDEX, Action::EDIT,
                    fn (Action $action): Action => $action->setLabel("Editer l'utilisateur")
                 )->update(Crud::PAGE_INDEX, Action::DELETE,
                    fn (Action $action): Action => $action->setLabel("Supprimer l'utilisateur")
                 )->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN,
                    fn (Action $action): Action => $action->setLabel("Sauvegarder l'utilisateur")
                 )->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE,
                    fn (Action $action): Action => $action->setLabel("Sauvegarder et continuer l'édition")
                 )->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                    fn (Action $action): Action => $action->setLabel("Créer l'utilisateur")
                 )->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER,
                    fn (Action $action): Action => $action->setLabel("Créer puis créer un autre utilisateur")
                 );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
                 ->setPageTitle("index", "Listing des utilisateurs")
                 ->setPageTitle("new", "Création d'un utilisateur")
                 ->setPageTitle("edit", "Edition d'un utilisateur")
                 ->setEntityLabelInSingular("Utilisateur")
                 ->setEntityLabelInPlural("Utilisateurs");
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('uid');
        yield ChoiceField::new('roles')->setLabel("Autorisation d'accès")
                                       ->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", false)
                                       ->setFormTypeOption('mapped', true)
                                       ->setFormTypeOption('extra_options', ['meteoAdminChoiceExtension' => true]) // ajout pour que l'extension ChoiceTypeExtensions s'execute (à la drupal)
                                       ->setChoices(UserRoles::$choix);
        yield TextField::new('displayName')->setLabel("Nom complet");
        yield TextField::new('mail')->setLabel("Courriel");
        yield BooleanField::new('recevoirMail')->hideOnIndex()->hideOnDetail()->hideOnForm();
        yield ChoiceField::new('eduPersonAffiliations')->setLabel("Affiliations")
                                       ->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", true)
                                       ->setFormTypeOption('mapped', true)
                                       ->setChoices(UserRoles::$easyAdminEduAffiliations);
    }
}
