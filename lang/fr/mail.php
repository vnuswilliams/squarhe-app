<?php

/**
 * Fichier : lang/fr/notifications.php
 *
 * Clés de traduction pour les notifications mail de l'application.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Suppression de compagnie
    |--------------------------------------------------------------------------
    */
    'delete_company' => [

        // Sujet du mail
        'subject' => 'Votre compagnie ":company" a été supprimée',

        // Salutation
        'greeting' => 'Bonjour :name,',

        // Introduction
        'intro' => 'Nous vous confirmons que votre compagnie **:company** a bien été supprimée de notre plateforme. '
            . 'Cette action a pris effet immédiatement.',

        // Bloc rétention
        'retention_title' => 'Suppression définitive dans :days jours',
        'retention_body'  => 'Conformément à notre politique de rétention des données, l\'ensemble des informations '
            . 'associées à cette compagnie sera **définitivement et irréversiblement supprimé** de nos bases de données '
            . 'le **:date**. Passé ce délai, aucune récupération ne sera possible.',

        // Section "Que va-t-il se passer ?"
        'what_happens_title' => 'Quelles données seront affectées ?',
        'what_happens_intro'  => 'Voici un récapitulatif des données liées à votre compagnie et leur sort après suppression définitive :',

        // Tableau des données
        'table_data'   => 'Données',
        'table_status' => 'Statut',

        'data_employees' => 'Fiches employés & contrats',
        'data_payrolls'  => 'Bulletins de paie & historique',
        'data_documents' => 'Documents & pièces jointes',
        'data_settings'  => 'Configuration & paramétrage',
        'data_account'   => 'Votre compte utilisateur personnel',

        'status_lost' => 'Supprimé définitivement',
        'status_kept' => 'Conservé',

        // Récupération
        'recover_title' => 'Vous avez changé d\'avis ?',
        'recover_body'  => 'Si cette suppression est une erreur ou si vous souhaitez récupérer vos données avant '
            . 'l\'échéance, contactez notre équipe support dans les plus brefs délais. '
            . 'Nous ferons notre possible pour vous aider dans ce délai de :days jours.',

        // CTA
        'cta' => 'Contacter le support',

        // Support
        'support_text'        => 'Vous pouvez également nous écrire directement à l\'adresse suivante :',
        'support_link_label'  => 'Écrire au support',

        // Au revoir
        'farewell' => 'Nous vous remercions de la confiance que vous nous avez accordée et espérons vous retrouver prochainement.',

        // Mention légale
        'legal_notice' => 'Cet e-mail a été envoyé automatiquement suite à la suppression de la compagnie ":company" '
            . 'le :date. Si vous n\'êtes pas à l\'origine de cette action, contactez immédiatement notre support.',
    ],

];