<?php

namespace App\Command;

use DateTime;
use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-json')]
class ImportJsonCommand extends Command
{
    public function __construct(private applicationRepository $applicationRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Importation des applications depuis le json ent')
             ->addArgument('fichier', InputArgument::REQUIRED, 'Fichier à ouvrir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fichier = $input->getArgument('fichier');
        $jsonTxt = file_get_contents($fichier);
        $jsonArray = json_decode($jsonTxt, true);

        $output->writeln([
            'Import des applications depuis un fichier JSON',
            '============',
            '',
        ]);

        $nbApplicationCrees = 0;
        $nbApplicationMaj = 0;
        foreach($jsonArray['APPS'] as $fname => $appArray)
        {
            if ($appArray['hide'] == true)
                continue;

            $categorie = self::trouverCategorieApp($fname, $jsonArray['LAYOUT']);

            if ($categorie == "__hidden__" || $categorie === null)
                continue;

            $application = $this->applicationRepository->findOneBy(['fname' => $fname]);

            $isUpdate = $application === null ? false : true;

            $application ??= new Application();

            $application->setCategorie($categorie);
            $application->setFname($fname);
            $application->setIsFromJson(true);
            $application->setTitle($appArray['title']);
            $application->setState('operational');

            if (array_key_exists('description', $appArray))
                $application->setDescription($appArray['description']);

            if (array_key_exists('url', $appArray))
                $application->setUrl($appArray['url']);

            $msg = $isUpdate ? "Mise à jour" : "Création";
            $msg .= "  Application : $fname ";

            $output->writeln($msg);

            $isUpdate ? $this->applicationRepository->updateApplication($application) : $this->applicationRepository->createApplication($application);
            $isUpdate ? $nbApplicationMaj++ : $nbApplicationCrees++;
        }

        $output->writeln([
            '',
            'Fin Import des applications',
            '============',
            "Crées : $nbApplicationCrees",
            "Mise à jour: $nbApplicationMaj",
        ]);

        return Command::SUCCESS;
    }

    private static function trouverCategorieApp($fname, $layoutArray) {
        foreach($layoutArray as $categorie => $arrayApps) {
            foreach($arrayApps as $appName) {
                if ($appName == $fname) {
                    return $categorie;
                }
            }
        }
        return null;
    }
}
