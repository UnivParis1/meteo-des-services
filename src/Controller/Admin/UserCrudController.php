<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Entity\User;
use App\Repository\ApplicationRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\HttpFoundation\RequestStack;

#[IsGranted('ROLE_SUPER_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private RequestStack $requestStack)
    {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('uuid');
        yield ArrayField::new('roles');
        yield TextField::new('displayName');
        yield TextField::new('mail');
        yield AssociationField::new('applications')
                            ->setFormTypeOption('placeholder', 'No applications managed')
                            ->setFormTypeOption('required', false);
    }
}
    