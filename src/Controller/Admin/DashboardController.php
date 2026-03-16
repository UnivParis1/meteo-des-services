<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('app_meteo');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Meteo Services');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkTo(UserCrudController::class, 'Gestion des Utilisateurs', 'fas fa-list');
        yield MenuItem::subMenu('Gestion des Applications', 'fas fa-list')->setSubItems([
            MenuItem::linkTo(ApplicationCrudController::class, 'Applications', 'fas fa-list'),
            MenuItem::linkTo(TagsCrudController::class, 'Gestion des Tags', 'fas fa-list'),
        ]);
    }
}
