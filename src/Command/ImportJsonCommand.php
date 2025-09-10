<?php

namespace App\Command;

use DateTime;
use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-json', description: "Importation des applications depuis le json ent")]
class ImportJsonCommand extends Command
{
    public function __construct(private applicationRepository $applicationRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Importation des applications depuis le json ent')
             ->addOption(name: 'fromENT')
             ->addArgument('uri', InputArgument::REQUIRED, 'Localisation de la ressource web/filesystem')
             ->addArgument('jsessionid', InputArgument::OPTIONAL);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $fromENT = $input->getOption('fromENT');
        $fichier = $input->getArgument('uri');

        if ($fromENT) {

            if (stream_is_local($fichier)) {
                $output->writeln("l'argument uri n'est pas une url");
                return Command::FAILURE;
            }

            $jsessionid = $input->getArgument('jsessionid');

            if (!$jsessionid) {
                $output->writeln('Pas de valeur donnée pour le cookie de session');
                return Command::FAILURE;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fichier);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Météo-service application']);
            curl_setopt($ch, CURLOPT_COOKIE, "JSESSIONID=$jsessionid");

            if(!curl_exec($ch)){
                die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            }
            else{
                $jsonTxt = curl_exec($ch);
            }
            curl_close($ch);
        } else {
            if ( ! stream_is_local($fichier)) {
                $output->writeln("l'argument uri n'est pas un fichier local");
                return Command::FAILURE;
            }

            $jsonTxt = file_get_contents($fichier);
        }

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

            $application = $this->applicationRepository->findOneBy(['title' => $appArray['title'] ]);

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
