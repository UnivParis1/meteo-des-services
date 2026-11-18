<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ApplicationService;
use App\Entity\Application;
use App\Repository\UserRepository;
use App\Repository\ApplicationRepository;
use DateTime;
use DateTimeImmutable;

#[AsCommand(
    name: 'app:zeroHistorique',
    description: "Ajoute une entrée application_history pour les applications ayant aucun historique (au moins la date d'entrée",
)]
class AppZeroHistoriqueCommand{

    public function __construct(private EntityManagerInterface $em,
                                private UserRepository $userRepository,
                                private ApplicationRepository $applicationRepository,
                                private ApplicationService $applicationService)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('createur de l\'historique de création de l\'application')] string $uid,
        #[Argument('date format 31/12/2000-14h30')] string $argDate = "now"
    ): int {

        $io->info(["Cette commande ajoute un enregistrement à la table application_history pour les applications qui n'ont pas d'historique",
                           "Exemple: php bin/console app:zeroHistorique eppnUser 01/01/1970-00h00"]);

        if (is_null($this->userRepository->findOneByUid($uid))) {
            $io->error("Pas d'utilisateur trouvé pour $uid");
            return Command::FAILURE;
        }

        $dt = ($argDate === "now") ? new DateTimeImmutable() : DateTimeImmutable::createFromFormat("d/m/Y H:i", str_replace('-', ' ', str_replace('h', ':', $argDate)));

        if ( ! ($dt && strlen($argDate) == 16 ) ) {
            $io->error("Argument date mis au mauvais format. Exemple de bon format: 24/07/2026-14h30");
            return Command::FAILURE;
        }

        $apps = $this->em->createQuery("SELECT a FROM App\Entity\Application a LEFT JOIN App\Entity\ApplicationHistory ah ON a.id=ah.application WHERE ah.id is NULL")->execute();

        $io->section("Applications sans historique");
        /* @var $apps Application[] */
        foreach ($apps as $application) {
            $history = $this->applicationService->updateHistoryObj($application, "creation");
            $history->setMessage("Création forcé par commande console symfony app:zeroHistorique");

            $history->setAuthor($uid);

            $history->setDate($dt);
            $this->applicationRepository->createHistory($history);

            $io->text("Ajout historique création pour application: fname => " . str_pad($application->getFname(),50, '_',  STR_PAD_RIGHT) . ", history_id => {$history->getId()}");
        }

        $io->success("OK tout s'est passé comme prévu");

        return Command::SUCCESS;
    }
}
