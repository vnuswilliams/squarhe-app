<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum DocumentAccessEnum: string
{
    use EnumTrait;
    // 🌐 Accessible à tous les utilisateurs de l'entreprise
    case ALL = 'all';

    // 👑 Administrateurs et propriétaire seulement
    case ADMIN = 'admin';

    // 👤 Propriétaire seulement (souvent l’employé lui-même)
    case OWNER = 'owner';

    // 🧑‍💼 Managers, Admins et Owner
    case MANAGER = 'manager';

    // 👔 Employé concerné, Admins et Owner
    case EMPLOYEE = 'employee';

     public function label(): string
    {
        return match ($this) {
            self::ALL => 'Accessible à tous les utilisateurs',
            self::ADMIN => 'Administrateurs et propriétaire uniquement',
            self::OWNER => 'Propriétaire uniquement',
            self::MANAGER => 'Managers, administrateurs et propriétaire',
            self::EMPLOYEE => 'Employé concerné, administrateurs et propriétaire',
        };
    }
}
