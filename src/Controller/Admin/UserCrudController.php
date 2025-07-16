<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[IsGranted('ROLE_SUPER_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('uid');
        yield ChoiceField::new('roles')->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", true)
                                       ->setFormTypeOption('mapped', true)
                                       ->setChoices(UserType::$choix);
        yield TextField::new('displayName');
        yield TextField::new('mail');
        yield BooleanField::new('recevoirMail');
        yield ChoiceField::new('eduPersonAffiliations')->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", true)
                                       ->setFormTypeOption('mapped', true)
                                       ->setChoices(UserType::$easyAdminEduAffiliations);
//        yield ChoiceField::new('eduPersonAffiliations')->setChoices(UserType::$easyAdminEduAffiliations);
    }
}
