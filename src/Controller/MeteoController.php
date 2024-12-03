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
        $application = new ArrayCollection();

        //Barre de recherche
        $searchApplication = new SearchApplication();
        $form = $this->createForm(SearchFormType::class, $searchApplication);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page = 1;
            $applications = $this->applicationService->getApplicationByFilters($searchApplication->searchTerm, $searchApplication->selectedState);
        } //Pas de recherche
        else {
            $applications = $this->applicationService->getAllApplications();
        }

        $applications = $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);

        //Pagination
        $limit = 10;
        $nbPage = ceil(count($applications) / $limit);

        if ($page === null or $page < 1 or $page > $nbPage and $nbPage != 0) {
            return $this->redirectToRoute('app_meteo', ['page' => 1]);
        }

        $debut = ($page * $limit) - $limit;
        $applicationsPaginate = array_slice($applications, $debut, $limit);

        return $this->render('meteo/index.html.twig', [
            'applications' => $applicationsPaginate,
            'form' => $form->createView(),
            'page' => $page,
            'nbPage' => $nbPage,
            'role' => $this->getParameter('global_variable')
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
}
