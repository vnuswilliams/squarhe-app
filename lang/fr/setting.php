<?php

return [
    'settingupdateheading' => 'Mettre à jour la compagnie',
    'settingupdatesubheading' => 'Mettez à jour les informations de votre compagnie.',
    'settingaddheading' => 'Ajouter votre entreprise',
    'settingaddsubheading' => 'Ajouter un entreprise pour pouvoir gérer vos ressources',
    'name' => 'Raison sociale de la compagnie *',
    'email' => 'Adresse mail de la compagnie *',
    'phone' => 'Numéro de tél de la compagnie *',
    'adresse' => 'Adresse de la compagnie *',
    'city' => "Ville d'activité de la compagnie *",
    'cnps' => 'N° CNPS',
    'niu' => 'N° NIU',
    'rccm' => 'N° RCCM',
    'dangerzone' => 'Zone de danger',
    'regencodesubtitle' => 'Si vous pensez que le code unique de votre entreprise a été corompu vous pouvez le changer.',
    'regencodebutton' => 'Regenerer le code de la compagnie',
    'deletecompanyheading' => 'Supprimer la compagnie ?',
    'confirmdeletion' => 'Confirmer la suppression',
    'cancelbutton' => 'Annuler',
    'deletecompanysubheading' => 'Êtes-vous sûr de vouloir supprimer cette entreprise ? Cette action est irréversible. Toutes les données associées seront perdues dans un délai de 15jours à compter du jour de la suppression.',
    'deletebutton' => "Supprimer l'entreprise",

    'common' => [
        'choose'  => 'Choisir une option',
        'enabled' => 'Activé',
    ],

    'actions' => [
        'save'   => 'Enregistrer',
        'cancel' => 'Annuler',
    ],

    'fiscal' => [
        'title'       => 'Configuration Fiscale & Sociale',
        'description' => 'Activez ou désactivez les éléments fiscaux applicables à votre société.',

        'rav' => [
            'label'       => 'RAV',
            'description' => 'Redevance Audio Visuel',
        ],
        'tdl' => [
            'label'       => 'TDL',
            'description' => 'Taxe de Développement Local',
        ],
        'irpp' => [
            'label'       => 'IRPP',
            'description' => 'Impôt sur le Revenu des Personnes Physiques',
        ],
    ],

    'holidays' => [
        'title'       => 'Jours Fériés',
        'description' => 'Gérez les jours fériés applicables à votre entreprise.',
        'add'         => 'Ajouter un jour férié',
        'remove'      => 'Supprimer ce jour férié',
        'empty'       => 'Aucun jour férié configuré.',
    ],

    'leave' => [
        'title'       => 'Congés & Heures de travail',
        'description' => 'Définissez les droits aux congés et le volume horaire mensuel de référence.',
        'monthly'        => 'Congé mensuel',
        'monthly_hint'   => 'Nombre de jours par mois',
        'seniority'      => 'Congé ancienneté',
        'seniority_hint' => 'Jours supplémentaires selon l\'ancienneté',
        'child'          => 'Congé enfant',
        'child_hint'     => 'Jours par enfant à charge',
    ],

    'labour' => [
        'hours_label'       => 'Heures mensuelles',
        'hours_description' => 'Volume horaire mensuel de référence',
    ],

    'contributions' => [
        'title'       => 'Cotisations & Primes',
        'description' => 'Activez les cotisations sociales et primes applicables à votre entreprise.',

        'seniority_bonus' => [
            'category'    => 'Prime',
            'label'       => 'Prime d\'ancienneté',
            'description' => 'Calculée selon les années d\'ancienneté du salarié',
        ],
        'old_age_pension' => [
            'category'    => 'Retraite',
            'label'       => 'Pension vieillesse',
            'description' => 'Cotisation pour la retraite des salariés',
        ],
        'family_allowances' => [
            'category'    => 'Famille',
            'label'       => 'Allocations familiales',
            'description' => 'Prestations versées aux familles avec enfants',
        ],
        'accident' => [
            'category'    => 'Assurance',
            'label'       => 'Accident de travail',
            'description' => 'Couverture des accidents survenus en milieu professionnel',
        ],
        'cfc' => [
            'category'    => 'Formation',
            'label'       => 'CFC',
            'description' => 'Crédit de Formation Continue',
        ],
        'cac' => [
            'category'    => 'Contribution',
            'label'       => 'CAC',
            'description' => 'Contribution à l\'Apprentissage et à la Formation',
        ],
        'fne' => [
            'category'    => 'Emploi',
            'label'       => 'FNE',
            'description' => 'Fonds National de l\'Emploi',
        ],
    ],

    'payment' => [
        'title'       => 'Paiement & Droit applicable',
        'description' => 'Configurez le mode de paiement des salaires et le cadre juridique applicable.',

        'method' => [
            'label'       => 'Mode de paiement',
            'description' => 'Moyen de paiement utilisé par défaut pour les salaires',
        ],
        'law' => [
            'label'       => 'Droit applicable',
            'description' => 'Cadre juridique régissant les contrats de travail',
        ],
    ],

];
