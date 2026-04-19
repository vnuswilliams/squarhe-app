<?php

use App\Enums\LawEnum;
use App\Enums\PaymentEnum;

return [
    'defaults' => [
        'rav' => true,
        'tdl' => true,
        'irpp' => true,
        'labourHours' => 173.33,
        'paymentMethod' => PaymentEnum::BANK_TRANSFERT->label(),
        'applicable_law' => LawEnum::LAW_WORK->label(),
        'seniorityBonus' => [
            'enabled' => true,
            'rate' => 0.02
        ],
        'familyAllowances' => [
            'enabled' => true,
            'rate' => 0.07
        ],
        'accident' => [
            'enabled' => true,
            'rate' => 0.0175
        ],
        'leaves' => [
            'monthlyLeave' => 1.5,
            'seniorLeave' => 2,
            'childLeave' => 2,
        ],
        'oldAgePension' => [
            'enabled' => true,
            'employerShare' => 0.042,
            'employeeShare' => 0.042,
        ],
        'cfc' => [
            'enabled' => true,
            'employerShare' => 0.015,
            'employeeShare' => 0.01,
        ],
        'cac' => [
            'enabled' => true,
            'employeeShare' => 0.1,
        ],

        'fne' => [
            'enabled' => true,
            'employerShare' => 0.01,
        ],
        'fixedHolidays' => [
        '01-01', // Nouvel An
        '02-11', // Fête jeunesse
        '05-01', // Fête du travail
        '05-20', // Fête nationale
        '08-15', // Assomption
        '12-25', // Noël
    ]
    ],
    'settingsCompany' => [
        'labourHours' => [
            'generalHours' => 173.33,
            'hospitalHours' => 195,
            'farmerHours' => 200,
            'restaurantHours' => 234,
            'securityHours' => 242.66,
        ],

        'familyAllowances' => [
            'general' => 0.07,
            'farmer' => 0.0565,
            'teaching' => 0.037,
        ],
        'accident' => [
            'default' => 0.0175,
            'low' => 0.0175,
            'meduim' => 0.025,
            'high' => 0.05,
        ],
    ],
    'fixedHolidays' => [
        '01-01', // Nouvel An
        '02-11', // Fête jeunesse
        '05-01', // Fête du travail
        '05-20', // Fête nationale
        '08-15', // Assomption
        '12-25', // Noël
    ]
];