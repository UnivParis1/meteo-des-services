<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Front\ApplicationsSorter;
use App\Model\SearchApplication;
use App\Service\ApplicationService;
use App\Service\UserService;
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

    #[Route('/meteo/{page<\d+>?1}', name: 'app_meteo')]
    public function index(int $page, Request $request): Response
    {
        $session = $request->getSession();

        $searchApplication = $session->get('searchApplication') ?? new SearchApplication();

        $form = $this->createForm(SearchFormType::class, $searchApplication);

        if (! $session->has('searchApplication') || $request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session->set('searchApplication', $searchApplication);
            }
        }

        $applications = $this->applicationService->getApplicationByFilters($searchApplication->searchTerm, $searchApplication->selectedState);
        $applications = $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);

        $nbApplications = count($applications);

        $limit = $nbApplications > 0 ? $searchApplication->limit ?? $nbApplications : 1;

        $nbPage = ceil(count($applications) / $limit);

        if ($page === null) {
            $session->remove('searchApplication');
            return $this->redirectToRoute('app_meteo');
        }

        $debut = $page * $limit - $limit;
        $applicationsPaginate = array_slice($applications, $debut, $limit);

        return $this->render('meteo/index.html.twig', [
            'applications' => $applicationsPaginate,
            'form' => $form->createView(),
            'page' => $page,
            'nbPage' => $nbPage
        ]);
    }

    #[Route('/meteo/application/{id}', name: 'app_application_details')]
    public function updateDetailsPopUp(int $id)
    {
        $application = $this->applicationService->getApplicationById($id);
        return new Response($this->renderView('meteo/pop-ups/details.html.twig', [
            'application' => $this->applicationService->convertToDTO($application, null)
        ]));
    }

    #[Route('/', name: 'homepage')]
    public function homepage()
    {
        return $this->redirectToRoute("app_meteo");
    }
}
