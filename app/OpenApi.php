<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Royal App API', description: 'API for mobile and integrations')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token'
)]
class OpenApi
{
}
