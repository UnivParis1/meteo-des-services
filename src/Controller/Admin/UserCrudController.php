<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[IsGranted('ROLE_SUPER_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);

        $crud->setHelp("edit", "Pour le champs applications, on peut rajouter des applications à l'utilisateur mais non lui en enlever. Pour enlever des applications, le seul moyen (bug), c'est de supprimer complétement l'utilisateur");

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('uid');
        yield ChoiceField::new('roles')->setFormType(ChoiceType::class)
                                       ->setFormTypeOption("expanded", false)
                                       ->setFormTypeOption("multiple", true)
                                       ->setFormTypeOption('mapped', true)
                                       ->setChoices(["user" => 'ROLE_USER', "admin" => 'ROLE_ADMIN', 'super_admin' => "ROLE_SUPER_ADMIN"]);
        yield TextField::new('displayName');
        yield TextField::new('mail');
        yield BooleanField::new('recevoirMail');
        yield AssociationField::new('applications')
                            ->setFormTypeOption('placeholder', 'No applications managed')
                            ->setFormTypeOption('required', false);
    }

    private function _saveApplicationsUser(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance)
    {
        if ($entityInstance->getApplications()->count() > 0) {
            foreach ($entityInstance->getApplications() as $application) {
                $application->setUser($entityInstance);
                $entityManager->persist($application);
            }
            $entityManager->flush();
        }
    }

    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->_saveApplicationsUser($entityManager, $entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        $this->_saveApplicationsUser($entityManager, $entityInstance);
    }
}