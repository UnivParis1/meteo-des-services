<?php

namespace App\Model;

class UserRoles
{
    public static array $choix = ['Anonyme' => 'PUBLIC_ACCESS', 'Etudiant' => 'ROLE_STUDENT', 'Enseignant' => 'ROLE_TEACHER', 'Biatssp' => 'ROLE_STAFF', 'Administrateur' => 'ROLE_ADMIN', 'Superviseur' => 'ROLE_SUPER_ADMIN'];
    public static array $easyAdminEduAffiliations = ['student' => 'student', 'teacher' => 'teacher', 'staff' => 'staff'];
}
