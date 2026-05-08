<?php

use Squarhe\Subscription\Models\Feature;
use Squarhe\Subscription\Models\FeatureConsumption;
use Squarhe\Subscription\Models\FeaturePlan;
use Squarhe\Subscription\Models\FeatureTicket;
use Squarhe\Subscription\Models\Plan;
use Squarhe\Subscription\Models\Subscription;
use Squarhe\Subscription\Models\SubscriptionRenewal;

return [
    'database' => [
        'cancel_migrations_autoloading' => false,
    ],

    'feature_tickets' => env('SOULBSCRIPTION_FEATURE_TICKETS', false),

    'models' => [

        'feature' => Feature::class,

        'feature_consumption' => FeatureConsumption::class,

        'feature_ticket' => FeatureTicket::class,

        'feature_plan' => FeaturePlan::class,

        'plan' => Plan::class,

        'subscriber' => [
            'uses_uuid' => env('SOULBSCRIPTION_SUBSCRIBER_USES_UUID', false),
        ],

        'subscription' => Subscription::class,

        'subscription_renewal' => SubscriptionRenewal::class,
    ],
];
