<?php

namespace App\Controller\Admin;

use App\Model\UserRoles;
use App\Entity\AppsEntGroupsRoles;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AppsEntGroupsRolesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AppsEntGroupsRoles::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('groupname');

        yield ChoiceField::new('role')->setLabel("Autorisation d'accès")
                                       ->setFormType(ChoiceType::class)
                                       ->setFormTypeOption('expanded', false)
                                       ->setFormTypeOption('multiple', false)
                                       ->setFormTypeOption('mapped', true)
                                       ->setChoices(UserRoles::$choix)
                                       ->setHelp("définis les autorisations d'accès à l'application de manière hiérarchique (ex: un enseignant a accès aux applications étudiantes, un biatssp aux applications enseignantes...)");


    }

}
