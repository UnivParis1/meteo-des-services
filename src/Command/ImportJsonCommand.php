<?php

namespace App\Command;

use App\Entity\Application;
use App\Entity\Tags;
use App\Repository\ApplicationRepository;
use App\Repository\TagsRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-json', description: 'Importation des applications depuis le json ent')]
class ImportJsonCommand extends Command
{
    public function __construct(private ApplicationRepository $applicationRepository,
        private TagsRepository $tagsRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Importation des applications depuis le json ent')
             ->addOption(name: 'fromENT')
             ->addOption('creerApp')
             ->addArgument('uri', InputArgument::REQUIRED, 'Localisation de la ressource web/filesystem')
             ->addArgument('bearerFile', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $fromENT = $input->getOption('fromENT');

        // creer les applications inexistantes
        $creerApp = $input->getOption('creerApp');
        $fichier = $input->getArgument('uri');

        if ($fromENT) {
            if (stream_is_local($fichier)) {
                $output->writeln("l'argument uri n'est pas une url");

                return Command::FAILURE;
            }

            $bearerFile = $input->getArgument('bearerFile');

            if (!is_file($bearerFile)) {
                $output->writeln("Le fichier bearer est absent du chemin pour: $bearerFile");

                return Command::FAILURE;
            }
            $bearerContent = file_get_contents($bearerFile);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fichier);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Météo-service application',
                "Authorization: Bearer $bearerContent"]);

            if (!curl_exec($ch)) {
                exit('Error: "'.curl_error($ch).'" - Code: '.curl_errno($ch));
            } else {
                $jsonTxt = curl_exec($ch);
            }
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (200 != $httpcode) {
                $output->writeln("Echec de récupération code erreur : $httpcode, réponse: $jsonTxt");

                return Command::FAILURE;
            }
        } else {
            if (!stream_is_local($fichier)) {
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
        foreach ($jsonArray['APPS'] as $fname => $appArray) {
            if (true == $appArray['hide']) {
                continue;
            }

            $categorie = self::trouverCategorieApp($fname, $jsonArray['LAYOUT']);

            if ('__hidden__' == $categorie || null === $categorie) {
                continue;
            }

            $application = $this->applicationRepository->findOneBy(['fname' => $fname]);

            $isUpdate = null === $application ? false : true;

            $application ??= new Application();

            $application->setCategorie($categorie);
            $application->setFname($fname);

            // maj uniquement en création pour éviter de remplacer des saisies
            if (!$isUpdate) {
                $application->setIsFromJson(true);
                $application->setTitle($appArray['title']);
                $application->setState('operational');
            }

            if (array_key_exists('description', $appArray) && null === $application->getDescription()) {
                $application->setDescription($appArray['description']);
            }

            if (array_key_exists('url', $appArray) && null === $application->getUrl()) {
                $application->setUrl($appArray['url']);
            }

            if (array_key_exists('tags', $appArray)) {
                $aTags = $appArray['tags'];

                // supprime tous les tags existants avant de les importer
                $this->applicationRepository->removeApplicationTags($application);

                foreach ($aTags as $tag) {
                    $tags = $this->tagsRepository->findOneBy(['name' => $tag]);

                    if (!$tags) {
                        $tags = new Tags();
                        $tags->setName($tag);
                        $this->tagsRepository->createTags($tags);
                    }
                    $application->addTag($tags);
                }
            }

            $msg = $isUpdate ? 'Mise à jour' : ($creerApp ? 'Création' : 'Sans création');
            $msg .= "  Application : $fname ";

            $output->writeln($msg);

            if (!$isUpdate && $creerApp) {
                $this->applicationRepository->createApplication($application);
            } elseif ($isUpdate) {
                $this->applicationRepository->updateApplication($application);
            }
            $isUpdate ? $nbApplicationMaj++ : $nbApplicationCrees++;
        }

        $output->writeln([
            '',
            'Fin Import des applications',
            '============',
            ($creerApp ? 'Crées' : 'Sans création').": $nbApplicationCrees",
            "Mise à jour: $nbApplicationMaj",
        ]);

        return Command::SUCCESS;
    }

    private static function trouverCategorieApp($fname, $layoutArray)
    {
        foreach ($layoutArray as $categorie => $arrayApps) {
            foreach ($arrayApps as $appName) {
                if ($appName == $fname) {
                    return $categorie;
                }
            }
        }

        return null;
    }
}
