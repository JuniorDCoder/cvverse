<?php

return [
    'api_key' => env('GEMINI_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-1.5-flash-latest'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
];
