<?php

namespace App\Model;

class UserRoles
{
    public static array $choix = ["niveau 0" => "PUBLIC_ACCESS", "niveau 1" => "ROLE_STUDENT", "niveau 2" => "ROLE_TEACHER", "niveau 3" => 'ROLE_STAFF', "niveau 4" => 'ROLE_ADMIN', 'niveau 5' => "ROLE_SUPER_ADMIN"];
    public static array $easyAdminEduAffiliations = ["student" => "student", "teacher" => "teacher", "staff" => 'staff'];
}