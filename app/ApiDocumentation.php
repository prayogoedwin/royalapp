<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'superadmin@royalapp.com'),
        new OA\Property(property: 'password', type: 'string', example: 'password')
    ]
)]
#[OA\Schema(
    schema: 'UpdateProfileRequest',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Joko Santoso'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'joko@royalapp.com')
    ]
)]
#[OA\Schema(
    schema: 'UpdatePasswordRequest',
    required: ['current_password', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'current_password', type: 'string', example: 'old-password'),
        new OA\Property(property: 'password', type: 'string', example: 'new-password-123'),
        new OA\Property(property: 'password_confirmation', type: 'string', example: 'new-password-123')
    ]
)]
#[OA\Schema(
    schema: 'OrderReportRequest',
    properties: [
        new OA\Property(property: 'km_awal', type: 'number', format: 'float', example: 145458),
        new OA\Property(property: 'km_akhir', type: 'number', format: 'float', example: 150480),
        new OA\Property(property: 'deliver_datetime', type: 'string', format: 'date-time', example: '2026-04-10T08:30:00+07:00'),
        new OA\Property(property: 'notes', type: 'string', example: 'Pasien diturunkan dengan aman'),
        new OA\Property(property: 'order_status_id', type: 'integer', example: 3)
    ]
)]
class ApiDocumentation
{
    #[OA\Post(
        path: '/api/login',
        tags: ['Auth'],
        summary: 'Login',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')),
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function login(): void {}

    #[OA\Post(path: '/api/logout', tags: ['Auth'], summary: 'Logout', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function logout(): void {}

    #[OA\Get(path: '/api/profile', tags: ['Profile'], summary: 'Get profile', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function profile(): void {}

    #[OA\Put(
        path: '/api/profile',
        tags: ['Profile'],
        summary: 'Update profile',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateProfileRequest')),
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function updateProfile(): void {}

    #[OA\Put(
        path: '/api/profile/password',
        tags: ['Profile'],
        summary: 'Update password',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdatePasswordRequest')),
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function updatePassword(): void {}

    #[OA\Get(path: '/api/stats/orders/total', tags: ['Stats'], summary: 'Count total order by user (cache)', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function totalOrders(): void {}

    #[OA\Get(path: '/api/stats/tasks/total', tags: ['Stats'], summary: 'Count total task by user', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function totalTasks(): void {}

    #[OA\Get(
        path: '/api/order-statuses',
        tags: ['Orders'],
        summary: 'Get order statuses',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function orderStatuses(): void {}

    #[OA\Get(
        path: '/api/orders',
        tags: ['Orders'],
        summary: 'Get order by user with pagination and status filter',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
            new OA\Parameter(name: 'order_status_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 3)),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Done'))
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function orders(): void {}

    #[OA\Get(
        path: '/api/orders/{order}',
        tags: ['Orders'],
        summary: 'Detail order',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function orderDetail(): void {}

    #[OA\Get(path: '/api/orders/{order}/photos', tags: ['Order Photos'], summary: 'List photos', security: [['sanctum' => []],], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    #[OA\Post(
        path: '/api/orders/{order}/photos',
        tags: ['Order Photos'],
        summary: 'Create photo',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['photo'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Foto Unit'),
                        new OA\Property(property: 'description', type: 'string', example: 'Foto sebelum berangkat'),
                        new OA\Property(property: 'photo', type: 'string', format: 'binary')
                    ]
                )
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    #[OA\Post(
        path: '/api/orders/{order}/photos/{photo}',
        tags: ['Order Photos'],
        summary: 'Update photo',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'photo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Foto Unit Update'),
                    new OA\Property(property: 'description', type: 'string', example: 'Foto update'),
                    new OA\Property(property: 'photo', type: 'string', format: 'binary')
                ])
            )
        ),
        responses: [new OA\Response(response: 200, description: 'Updated')]
    )]
    #[OA\Delete(path: '/api/orders/{order}/photos/{photo}', tags: ['Order Photos'], summary: 'Delete photo', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'photo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function orderPhotos(): void {}

    #[OA\Get(path: '/api/orders/{order}/expenses', tags: ['Order Expenses'], summary: 'List expenses', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    #[OA\Post(
        path: '/api/orders/{order}/expenses',
        tags: ['Order Expenses'],
        summary: 'Create expense',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['expense_category', 'amount'],
                    properties: [
                        new OA\Property(property: 'expense_category', type: 'string', example: 'solar'),
                        new OA\Property(property: 'description', type: 'string', example: 'Isi BBM'),
                        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 250000),
                        new OA\Property(property: 'receipt_photo', type: 'string', format: 'binary')
                    ]
                )
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    #[OA\Put(path: '/api/orders/{order}/expenses/{expense}', tags: ['Order Expenses'], summary: 'Update expense', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'expense', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    #[OA\Delete(path: '/api/orders/{order}/expenses/{expense}', tags: ['Order Expenses'], summary: 'Delete expense', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'expense', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function orderExpenses(): void {}

    #[OA\Put(
        path: '/api/orders/{order}/report',
        tags: ['Order Report'],
        summary: 'Update order report',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: false, content: new OA\JsonContent(ref: '#/components/schemas/OrderReportRequest')),
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function orderReport(): void {}

    #[OA\Get(path: '/api/orders/{order}/etolls', tags: ['Order Etoll'], summary: 'List etoll transactions', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    #[OA\Post(path: '/api/orders/{order}/etolls', tags: ['Order Etoll'], summary: 'Create etoll transaction', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 201, description: 'Created')])]
    #[OA\Put(path: '/api/orders/{order}/etolls/{trx}', tags: ['Order Etoll'], summary: 'Update etoll transaction', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'trx', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    #[OA\Delete(path: '/api/orders/{order}/etolls/{trx}', tags: ['Order Etoll'], summary: 'Delete etoll transaction', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'trx', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function orderEtolls(): void {}

    #[OA\Get(path: '/api/orders/{order}/vehicle-issues', tags: ['Order Vehicle Issues'], summary: 'List vehicle issues', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    #[OA\Post(path: '/api/orders/{order}/vehicle-issues', tags: ['Order Vehicle Issues'], summary: 'Create vehicle issue', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 201, description: 'Created')])]
    #[OA\Put(path: '/api/orders/{order}/vehicle-issues/{issue}', tags: ['Order Vehicle Issues'], summary: 'Update vehicle issue', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'issue', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    #[OA\Delete(path: '/api/orders/{order}/vehicle-issues/{issue}', tags: ['Order Vehicle Issues'], summary: 'Delete vehicle issue', security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'issue', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function orderVehicleIssues(): void {}
}
