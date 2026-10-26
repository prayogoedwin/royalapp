<?php

return [

    'enabled' => env('FIREBASE_ENABLED', false),

    'project_id' => env('FIREBASE_PROJECT_ID', 'royale-app-449c3'),

    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/private/firebase-service-account.json')),

];
