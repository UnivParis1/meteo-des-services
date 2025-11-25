<?php

namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:addUserOrGroup',
    description: "Ajout d'un utilisateur ou d'un groupe d'utilisateurs par recherche wsgroups"
)]
class AddUsersOrUsersingroupCommand extends Command
{
    public function __construct(private UserService $userService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('userorgroup', InputArgument::REQUIRED, "User à rajouter ou Groupe d'utilisateurs");
        $this->addArgument('role', InputArgument::REQUIRED, 'Role assigné a(aux) utilisateur(s)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userorgroup = $input->getArgument('userorgroup');
        $role = $input->getArgument('role');

        if (!$userorgroup) {
            $io->error("Pas d'utilisateur ou de Groupe fourni");

            return Command::INVALID;
        }

        $users = $this->userService->searchUserOrGroup($userorgroup);

        if ($users && count($users) > 0) {
            foreach ($users as $user) {
                $io->info("Ajout ou Mise à jour de {$user->uid}");
                $this->userService->createOrUpdateUserRole($user, $role);
            }

            $io->success('OK import');

            return Command::SUCCESS;
        }

        $io->error("Pas d'utilisateurs importés");

        return Command::INVALID;
    }
}
