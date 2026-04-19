<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum CompanyRoleEnum: string
 {
use EnumTrait;
    use enumTrait;
    case OWNER = 'owner' ;
    case ADMIN = 'admin' ;
    case MANAGER = 'manager' ;
}