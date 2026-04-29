<?php

return [
    'settingupdateheading' => 'Update Company Profile',
    'settingupdatesubheading' => 'Update your company information.',
    'settingaddheading' => 'Add a Company',
    'settingaddsubheading' => 'Create a company to start managing your resources.',
    'name' => 'Company Legal Name *',
    'email' => 'Company Email Address *',
    'phone' => 'Company Phone Number *',
    'adresse' => 'Company Address *',
    'city' => 'City of Operation *',
    'cnps' => 'CNPS Registration Number',
    'niu' => 'Tax Identification Number (NIU)',
    'rccm' => 'Business Registration Number (RCCM)',
    'dangerzone' => 'Danger Zone',
    'regencodesubtitle' => 'If you believe your company’s unique code has been compromised, you can regenerate it.',
    'regencodebutton' => 'Regenerate Company Code',
    'deletecompanyheading' => 'Delete Company?',
    'confirmdeletion' => 'Confirm Deletion',
    'cancelbutton' => 'Cancel',
    'deletecompanysubheading' => 'Are you sure you want to delete this company? This action is irreversible. All associated data will be permanently deleted after 15 days.',
    'deletebutton' => 'Delete Company',

    'common' => [
        'choose'  => 'Select an option',
        'enabled' => 'Enabled',
    ],

    'actions' => [
        'save'   => 'Save',
        'cancel' => 'Cancel',
    ],

    'fiscal' => [
        'title'       => 'Tax & Social Settings',
        'description' => 'Enable or disable tax and social contributions applicable to your company.',

        'rav' => [
            'label'       => 'RAV',
            'description' => 'Broadcasting License Fee',
        ],
        'tdl' => [
            'label'       => 'TDL',
            'description' => 'Local Development Tax',
        ],
        'irpp' => [
            'label'       => 'IRPP',
            'description' => 'Personal Income Tax',
        ],
    ],

    'holidays' => [
        'title'       => 'Public Holidays',
        'description' => 'Manage public holidays applicable to your company.',
        'add'         => 'Add Public Holiday',
        'remove'      => 'Remove this holiday',
        'empty'       => 'No public holidays configured.',
    ],

    'leave' => [
        'title'       => 'Leave & Working Hours',
        'description' => 'Define leave entitlements and standard monthly working hours.',
        'monthly'        => 'Monthly Leave',
        'monthly_hint'   => 'Number of days per month',
        'seniority'      => 'Seniority Leave',
        'seniority_hint' => 'Additional days based on years of service',
        'child'          => 'Child Leave',
        'child_hint'     => 'Days granted per dependent child',
    ],

    'labour' => [
        'hours_label'       => 'Monthly Working Hours',
        'hours_description' => 'Standard monthly working hours',
    ],

    'contributions' => [
        'title'       => 'Contributions & Benefits',
        'description' => 'Enable social contributions and employee benefits applicable to your company.',

        'seniority_bonus' => [
            'category'    => 'Bonus',
            'label'       => 'Seniority Bonus',
            'description' => 'Calculated based on the employee’s years of service',
        ],
        'old_age_pension' => [
            'category'    => 'Retirement',
            'label'       => 'Old-Age Pension',
            'description' => 'Employee retirement contribution',
        ],
        'family_allowances' => [
            'category'    => 'Family',
            'label'       => 'Family Allowances',
            'description' => 'Benefits paid to employees with dependent children',
        ],
        'accident' => [
            'category'    => 'Insurance',
            'label'       => 'Workplace Accident Insurance',
            'description' => 'Coverage for work-related accidents',
        ],
        'cfc' => [
            'category'    => 'Training',
            'label'       => 'CFC',
            'description' => 'Continuing Professional Training Fund',
        ],
        'cac' => [
            'category'    => 'Contribution',
            'label'       => 'CAC',
            'description' => 'Apprenticeship and Training Contribution',
        ],
        'fne' => [
            'category'    => 'Employment',
            'label'       => 'FNE',
            'description' => 'National Employment Fund',
        ],
    ],

    'payment' => [
        'title'       => 'Payroll & Legal Framework',
        'description' => 'Configure salary payment methods and applicable legal framework.',

        'method' => [
            'label'       => 'Payment Method',
            'description' => 'Default method used to pay salaries',
        ],
        'law' => [
            'label'       => 'Applicable Law',
            'description' => 'Legal framework governing employment contracts',
        ],
    ],

];