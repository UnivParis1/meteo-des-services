<?php

namespace App\Model;

class UserRoles
{
    public static array $choix = ["student" => "ROLE_STUDENT", "teacher" => "ROLE_TEACHER", "staff" => 'ROLE_STAFF', "admin" => 'ROLE_ADMIN', 'super_admin' => "ROLE_SUPER_ADMIN"];
    public static array $choixTest = ["ROLE_STUDENT" => "ROLE_STUDENT", "ROLE_TEACHER" => "ROLE_TEACHER", "ROLE_STAFF" => 'ROLE_STAFF', "ROLE_ADMIN" => 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN' => "ROLE_SUPER_ADMIN"];
    public static array $easyAdminEduAffiliations = ["student" => "student", "teacher" => "teacher", "staff" => 'staff'];
    public static array $level = ["Niveau 0" => "student", "Niveau 1" => "teacher", 'Niveau 2' => "staff", 'Niveau 3' => "admin", "Niveau 4" => 'super_admin'];
}