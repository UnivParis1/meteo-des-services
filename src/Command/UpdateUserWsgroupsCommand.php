<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-user-wsgroups',
    description: 'Attribution des roles aux utilisateurs',
)]
class UpdateUserWsgroupsCommand extends Command
{
    public function __construct(private UserService $userService, private UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('uid', InputArgument::OPTIONAL, 'utilisateur à mettre à jour');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $uid = $input->getArgument('uid');

        if ($uid) {
          $user = $this->userRepository->findOneBy(['uid' => $uid]);

          if ( ! $user) {
            $io->error("uid $uid n'existe pas en base");
            return 2;
          }

          $users = [$user];
        }
        else {
          $users = $this->userRepository->findAll();
        }

        foreach ($users as $user) {
          $user->getNiveauACL();

          die("okusr");

          $roles = $user->getRoles();

          // Si un ancien role ROLE_USER est affécté, le changer par ROLE_STAFF
          $idxRoleUser = array_search("ROLE_USER", $roles);

          if ($idxRoleUser !== false) {
            $roles[$idxRoleUser] = "ROLE_STAFF";
            $user->setRoles($roles);
            $this->userRepository->updateUser($user);
          }

          $output->writeln("Mise à jour de {$user->getUid()}");

          $user = $this->userService->updateUserRequestInfos($user);

          $this->userRepository->updateUser($user);
        }

        $io->success('Utilisateurs mis à jours pour roles et eduPrincipalAffiliation');

        return Command::SUCCESS;
    }
}
