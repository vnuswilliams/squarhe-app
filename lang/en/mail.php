<?php

/**
 * File: lang/en/notifications.php
 *
 * Translation keys for application email notifications.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Company Deletion
    |--------------------------------------------------------------------------
    */
    'delete_company' => [

        // Email subject
        'subject' => 'Your company ":company" has been deleted',

        // Greeting
        'greeting' => 'Hello :name,',

        // Introduction
        'intro' => 'We confirm that your company **:company** has been successfully deleted from our platform. '
            . 'This action took effect immediately.',

        // Retention block
        'retention_title' => 'Permanent deletion in :days days',
        'retention_body'  => 'In accordance with our data retention policy, all information associated with this company '
            . 'will be **permanently and irreversibly deleted** from our databases on **:date**. '
            . 'After this date, recovery will no longer be possible.',

        // "What happens" section
        'what_happens_title' => 'What data will be affected?',
        'what_happens_intro'  => 'Below is a summary of the data linked to your company and what will happen to it after permanent deletion:',

        // Data table
        'table_data'   => 'Data',
        'table_status' => 'Status',

        'data_employees' => 'Employee records & contracts',
        'data_payrolls'  => 'Payslips & history',
        'data_documents' => 'Documents & attachments',
        'data_settings'  => 'Configuration & settings',
        'data_account'   => 'Your personal user account',

        'status_lost' => 'Permanently deleted',
        'status_kept' => 'Retained',

        // Recovery section
        'recover_title' => 'Changed your mind?',
        'recover_body'  => 'If this deletion was a mistake or if you would like to recover your data before the deadline, '
            . 'please contact our support team as soon as possible. '
            . 'We will do our best to assist you within this :days-day period.',

        // CTA
        'cta' => 'Contact Support',

        // Support
        'support_text'        => 'You can also reach us directly at the following address:',
        'support_link_label'  => 'Email Support',

        // Farewell
        'farewell' => 'Thank you for your trust. We hope to serve you again in the future.',

        // Legal notice
        'legal_notice' => 'This email was automatically sent following the deletion of the company ":company" '
            . 'on :date. If you did not initiate this action, please contact our support team immediately.',
    ],

];