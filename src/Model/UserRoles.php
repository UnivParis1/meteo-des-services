<?php

namespace App\Model;

class UserRoles
{
    public static array $choix = ['Anonyme' => 'PUBLIC_ACCESS', 'Etudiant' => 'ROLE_STUDENT', 'Enseignant' => 'ROLE_TEACHER', 'Biatssp' => 'ROLE_STAFF', 'Administrateur' => 'ROLE_ADMIN', 'Superviseur' => 'ROLE_SUPER_ADMIN'];
    public static array $choicesEduAffiliations = ['student' => 'student', 'teacher' => 'teacher', 'staff' => 'staff'];
    public static array $droitsAdminEtSuperviseur = ['estAdmin' => 'ROLE_ADMIN', 'estSuperviseur' => 'ROLE_SUPER_ADMIN'];

    public static function roleMaxHorsAdmins($eduPersonAffiliations): ?string {
        $affiliations = array_values(self::$choicesEduAffiliations);

        // la référence se trouve dans UserRoles, attention PUBLIC_ACCESS n'est pas compris d'où idx + 1
        for ($idx = count($affiliations) - 1; $idx >= 0; $idx--)
            if (in_array($affiliations[$idx], $eduPersonAffiliations))
                return array_values(self::$choix)[$idx + 1];
        return null;
    }


}
