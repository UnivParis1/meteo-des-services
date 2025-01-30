<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Front\ApplicationsSorter;
use App\Model\SearchApplication;
use App\Service\ApplicationService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MeteoController extends AbstractController
{
    public function __construct(public ApplicationService $applicationService,
                                public ApplicationsSorter $applicationsSorter)
    {
    }

    #[Route('/meteo/{page}', name: 'app_meteo')]
    public function index(int $page = null, Request $request): Response
    {
        //Barre de recherche
        $searchApplication = new SearchApplication();
        $form = $this->createForm(SearchFormType::class, $searchApplication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $applications = $this->applicationService->getApplicationByFilters($searchApplication->searchTerm, $searchApplication->selectedState);
        } //Pas de recherche
        else {
            $applications = $this->applicationService->getAllApplications();
        }

        $applications = $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);
        $nbApplications = count($applications);

        $limit = $searchApplication->limit ?? $nbApplications;
        $nbPage = ceil(count($applications) / $limit);

        if ($page === null) {
            return $this->redirectToRoute('app_meteo', ['page' => 1]);
        }

        $debut = ($page * $limit) - $limit;
        $applicationsPaginate = array_slice($applications, $debut, $limit);

        $role = 'admin';
        return $this->render('meteo/index.html.twig', [
            'applications' => $applicationsPaginate,
            'form' => $form->createView(),
            'page' => $page,
            'nbPage' => $nbPage,
            'role' => $role
        ]);
    }

    #[Route('/meteo/application/{id}', name: 'app_application_details')]
    public function updateDetailsPopUp(int $id)
    {
        $application = $this->applicationService->getApplicationById($id);
        return new Response($this->renderView('meteo/pop-ups/details.html.twig', [
            'application' => $this->applicationService->convertToDTO($application)
        ]));
    }

    #[Route('/', name: 'homepage')]
    public function homepage()
    {
        return $this->redirectToRoute("app_meteo", ['page' => 1]);
    }
}
