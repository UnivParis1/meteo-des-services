<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Application;
use App\Entity\Tags;
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
        yield MenuItem::linkToCrud('Gestion des Utilisateurs', 'fas fa-list', User::class);
        yield MenuItem::subMenu('Gestion des Applications', 'fas fa-list')->setSubItems([
                MenuItem::linkToCrud('Applications', 'fas fa-list', Application::class),
                MenuItem::linkToCrud('Gestion des Tags', 'fas fa-list', Tags::class)
        ]);
    }
}
