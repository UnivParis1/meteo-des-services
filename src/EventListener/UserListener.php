<?php

namespace App\EventListener;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserListener extends AbstractController
{
    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('eduPersonAffiliations')) {
            if ($event->hasChangedField('roles'))
                $roles = $this->getRolesValue($event->getNewValue('roles'));
            else
                $roles = $this->getRolesValue($user->getRoles());

            $eduPersonAffiliations = $event->getNewValue('eduPersonAffiliations');

            $this->testAndAssignRoles($user, $roles, $eduPersonAffiliations);
        }
    }

    public function prePersist(User $user, PrePersistEventArgs $event): void
    {
        $roles = $this->getRolesValue($user->getRoles());

        $eduPersonAffilations = $user->getEduPersonAffiliations();

        $this->testAndAssignRoles($user, $roles, $eduPersonAffilations);
    }

    private function getRolesValue($roles): array
    {
        $a = [];

        foreach ($roles as $role) {
            $key = array_search($role, UserType::$choix);
            if ($key !== false) {
                $a[] = $key;
            }
        }
        return $a;
    }

    private function getMaxAclRole(array $existingRoles) : ?array
    {
        $refAffiliations = array_values(UserType::$easyAdminEduAffiliations);

        $idx = count($refAffiliations);
        foreach (array_reverse($refAffiliations) as $affiliation) {
            foreach($existingRoles as $roleTest) {
                if ($roleTest == $affiliation) {
                    return [$idx => $affiliation];
                }
            }
            $idx--;
        }

        return null;
    }

    private function testAndAssignRoles(User $user, array $existingRoles, array $eduPersonAffilations): void
    {
        $exist = $this->getMaxAclRole($existingRoles);

        if ( ! $exist)
            return;

        $edu = $this->getMaxAclRole($eduPersonAffilations);

        $idxExist = array_key_first($exist);
        $idxEdu = array_key_first($edu);

        $eduRole = array_pop($edu);
        // si les données eduAffiliations récupérées sur wsgroups donne un droit plus élevé que les droits existants, assigner celui-ci à l'utilisateur
        if (! ($edu && $idxEdu > $idxExist ) )
            return;

        $role = UserType::$choix[$eduRole];
        $user->setRoles([$role]);
    }
}
