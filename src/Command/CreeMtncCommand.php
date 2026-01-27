<?php

namespace App\Command;

use App\Controller\MeteoController;
use App\Entity\Maintenance;
use App\EventSubscriber\MeteoCheckerListener;
use App\Service\ApplicationService;
use App\Service\MaintenanceService;
use DateTime;
use DateMalformedStringException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cree-mtnc',
    description: 'Insère une maintenance pour une application sans restriction de dates',
)]
class CreeMtncCommand extends Command
{
    public function __construct(private ApplicationService $applicationService, private MaintenanceService $maintenanceService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('appid', InputArgument::REQUIRED, "ID de l'application pour laquelle on crée une maintenance")
            ->addArgument('from', InputArgument::REQUIRED, 'Début de maintenance')
            ->addArgument('to', InputArgument::REQUIRED, 'Fin de maintenance')
            ->addArgument('state', InputArgument::REQUIRED, "Etat de l'application pendant la maintenance")
            ->addArgument('description', InputArgument::OPTIONAL, 'Description optionnelle de la maintenance');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $appId = $input->getArgument('appid');
        $from = $input->getArgument('from');
        $to= $input->getArgument('to');

        if (! is_numeric($appId)) {
            $io->error("appid: $appId n'est pas numérique !");
            return Command::INVALID;
        }

        try {
            $dtFrom = new DateTime($from);
            $dtTo = new DateTime($to);
        } catch (DateMalformedStringException $ex) {
            $io->error("Mauvais formattage du datetime from et/ou to");
            return Command::INVALID;
        }

        $state = $input->getArgument('state');

        MeteoController::$iconsName;


        $application = $this->applicationService->getApplicationById($appId);

        if (!$application) {
            $io->error("Application $appId non trouvée");
            return Command::INVALID;
        }

        $mtnc = new Maintenance();

        $mtnc->setApplication($application);
        $mtnc->setStartingDate($dtFrom);
        $mtnc->setEndingDate($dtTo);
        $mtnc->setApplicationState();

        if ($message = $input->getArgument('description'))
            $mtnc->setMessage($message);

        $this->maintenanceService->createMaintenance($mtnc);

        $io->success('Maitenance crée avec succées');

        return Command::SUCCESS;
    }
}
