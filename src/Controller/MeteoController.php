<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Front\ApplicationsSorter;
use App\Model\SearchApplication;
use App\Model\IconsName;
use App\Service\ApplicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/meteo', name: 'app_')]
class MeteoController extends AbstractController
{
    public function __construct(
        public ApplicationService $applicationService,
        public ApplicationsSorter $applicationsSorter,
    ) {}


    #[Route('/{page<\d+>?1}', name: 'meteo')]
    public function index(int $page, Request $request): Response
    {
        $session = $request->getSession();

        $searchApplication = $session->get('searchApplication') ?? new SearchApplication();

        $form = $this->createForm(SearchFormType::class, $searchApplication);

        if (!$session->has('searchApplication') || $request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session->set('searchApplication', $searchApplication);
            }
        }

        $applications = $this->applicationService->getApplicationByTag($searchApplication->searchTerm);
        $applications += $this->applicationService->getApplicationByFilters($searchApplication->searchTerm, $searchApplication->selectedState);

        $this->applicationsSorter->sortApplicationsByStateAndLastUpdate($applications);

        $nbApplications = count($applications);

        $limit = $nbApplications > 0 ? $searchApplication->limit ?? $nbApplications : 1;

        $nbPage = ceil(count($applications) / $limit);

        if (null === $page) {
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
            'iconsName' => IconsName::$iconsName,
        ]);
    }
}
