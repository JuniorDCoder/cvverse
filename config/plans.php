<?php

return [
    'free' => [
        'limits' => [
            'cvs' => 1,
            'cover_letters' => 2,
            'job_applications' => 10,
            'ai_messages_per_day' => 20,
        ],
        'features' => [
            'ai_assistant' => true,
            'ai_cv_generation' => false,
            'ai_cover_letter_generation' => false,
            'ai_job_analysis' => false,
            'premium_templates' => false,
        ],
    ],

    'paid_default' => [
        'limits' => [
            'cvs' => null,
            'cover_letters' => null,
            'job_applications' => null,
            'ai_messages_per_day' => null,
        ],
        'features' => [
            'ai_assistant' => true,
            'ai_cv_generation' => true,
            'ai_cover_letter_generation' => true,
            'ai_job_analysis' => true,
            'premium_templates' => true,
        ],
    ],

    // Override specific plans if needed.
    'overrides' => [
        'starter-monthly-xaf' => [
            'limits' => [
                'cvs' => 5,
                'cover_letters' => 10,
                'job_applications' => 50,
                'ai_messages_per_day' => 120,
            ],
            'features' => [
                'ai_assistant' => true,
                'ai_cv_generation' => true,
                'ai_cover_letter_generation' => true,
                'ai_job_analysis' => false,
                'premium_templates' => true,
            ],
        ],
        'starter-monthly-usd' => [
            'limits' => [
                'cvs' => 5,
                'cover_letters' => 10,
                'job_applications' => 50,
                'ai_messages_per_day' => 120,
            ],
            'features' => [
                'ai_assistant' => true,
                'ai_cv_generation' => true,
                'ai_cover_letter_generation' => true,
                'ai_job_analysis' => false,
                'premium_templates' => true,
            ],
        ],
        'pro-monthly-xaf' => [
            'limits' => [
                'cvs' => null,
                'cover_letters' => null,
                'job_applications' => null,
                'ai_messages_per_day' => null,
            ],
        ],
        'pro-yearly-xaf' => [
            'limits' => [
                'cvs' => null,
                'cover_letters' => null,
                'job_applications' => null,
                'ai_messages_per_day' => null,
            ],
        ],
        'lifetime-xaf' => [
            'limits' => [
                'cvs' => null,
                'cover_letters' => null,
                'job_applications' => null,
                'ai_messages_per_day' => null,
            ],
        ],
        'pro-monthly-usd' => [
            'limits' => [
                'cvs' => null,
                'cover_letters' => null,
                'job_applications' => null,
                'ai_messages_per_day' => null,
            ],
        ],
        'pro-yearly-usd' => [
            'limits' => [
                'cvs' => null,
                'cover_letters' => null,
                'job_applications' => null,
                'ai_messages_per_day' => null,
            ],
        ],
    ],
];
