<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Front\ApplicationsSorter;
use App\Model\SearchApplication;
use App\Service\ApplicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MeteoController extends AbstractController
{
    public array $iconsName = ['operational' => ['Opérationnel', 'text-success', 'sun'      , 18, 'M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"'],
                               'perturbed'   => ['Perturbé',     'text-warning', 'cloud'    , 16, 'M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383'],
                               'unavailable' => ['Indisponible', 'text-danger' , 'lightning', 16, 'M5.52.359A.5.5 0 0 1 6 0h4a.5.5 0 0 1 .474.658L8.694 6H12.5a.5.5 0 0 1 .395.807l-7 9a.5.5 0 0 1-.873-.454L6.823 9.5H3.5a.5.5 0 0 1-.48-.641z'],
                               'default'     => ['Non renseigné', ''           , 'grey'     , 16, 'M4.475 5.458c-.284 0-.514-.237-.47-.517C4.28 3.24 5.576 2 7.825 2c2.25 0 3.767 1.36 3.767 3.215 0 1.344-.665 2.288-1.79 2.973-1.1.659-1.414 1.118-1.414 2.01v.03a.5.5 0 0 1-.5.5h-.77a.5.5 0 0 1-.5-.495l-.003-.2c-.043-1.221.477-2.001 1.645-2.712 1.03-.632 1.397-1.135 1.397-2.028 0-.979-.758-1.698-1.926-1.698-1.009 0-1.71.529-1.938 1.402-.066.254-.278.461-.54.461h-.777ZM7.496 14c.622 0 1.095-.474 1.095-1.09 0-.618-.473-1.092-1.095-1.092-.606 0-1.087.474-1.087 1.091S6.89 14 7.496 14']
                              ];
    public function __construct(public ApplicationService $applicationService,
                                public ApplicationsSorter $applicationsSorter)
    {
    }

    #[Route('/meteo/api', name: 'api_meteo', format: 'json')]
    public function api_index(Request $request): JsonResponse
    {
        $fname = $request->get('fname', '');
        $selectedState = $request->get('state', '');

        $applications = $this->applicationService->getApplicationByFilters($fname, $selectedState);
        $applications = $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);

        return new JsonResponse($applications);
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

        $applications = $this->applicationService->getApplicationByTag($searchApplication->searchTerm);
        $applications = $applications + $this->applicationService->getApplicationByFilters($searchApplication->searchTerm, $searchApplication->selectedState);
        $applications = $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);

        $nbApplications = count($applications);

        $limit = $nbApplications > 0 ? $searchApplication->limit ?? $nbApplications : 1;

        $nbPage = ceil(count($applications) / $limit);

        if ($page === null) {
            $session->remove('searchApplication');
            return $this->redirectToRoute('app_meteo');
        }
        if ($nbPage > 0 && $nbPage < $page) {
            return $this->redirectToRoute('app_meteo', ['page' => 1]);
        }

        $debut = $page * $limit - $limit;
        $applicationsPaginate = array_slice($applications, $debut, $limit);

        return $this->render('meteo/index.html.twig', [
            'applications' => $applicationsPaginate,
            'form' => $form->createView(),
            'page' => $page,
            'nbPage' => $nbPage,
            'iconsName' => $this->iconsName
        ]);
    }

    #[Route('/meteo/application/{id}', name: 'app_application_details')]
    public function updateDetailsPopUp(int $id)
    {
        $application = $this->applicationService->getApplicationById($id);
        $histories = null;

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $histories = $application->getHistories();
        }

        return new Response($this->renderView('meteo/pop-ups/details.html.twig', [
            'application' => $this->applicationService->convertToDTO($application, null),
            'histories' => $histories,
            'iconsName' => $this->iconsName
        ]));
    }

    #[Route('/', name: 'homepage')]
    public function homepage()
    {
        return $this->redirectToRoute("app_meteo");
    }
}
