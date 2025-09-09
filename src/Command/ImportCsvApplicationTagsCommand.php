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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-csv-application_tags',
    description: 'Add a short description for your command',
)]
class ImportCsvApplicationTagsCommand extends Command
{
    public function __construct(private applicationRepository $applicationRepository,
                                private TagsRepository $tagsRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'csv file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        $content = file_get_contents($file);

        foreach (explode("\n", $content) as $line) {
            $aAppTags = explode(',', $line);

            if (count($aAppTags) != 2)
                continue;

            $fname = $aAppTags[0];
            $tag = $aAppTags[1];

            $application = $this->applicationRepository->findOneBy(['fname' => $fname]);

            if (!$application)
                continue;

            $tags = $this->tagsRepository->findOneBy(['name' => $tag]);

            if (!$tags) {
                $tags = new Tags();
                $tags->setName($tag);
                $this->tagsRepository->createTags($tags);
            }

            $appTags = $application->getTags();

            $test = false;
            foreach ($appTags as $appTag) {
                if ($appTag->getName() == $tags->getName()) {
                    $test = true;
                    break;
                }
            }

            if ($test)
                continue;

            $output->writeln("Import du tag {$tags->getName()} pour {$application->getFname()}");

            $application->addTag($tags);
            $this->applicationRepository->updateApplication($application);
        }

        $io->success("OK le script s'est bien déroulé");

        return Command::SUCCESS;
    }
}
