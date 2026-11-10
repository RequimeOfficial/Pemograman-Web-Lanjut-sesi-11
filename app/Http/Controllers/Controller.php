<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Informasi global dokumentasi OpenAPI: judul, server, skema keamanan Bearer, tag,
 * dan schema umum yang dipakai banyak endpoint.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'Perpustakaan API',
    description: 'REST API Laravel 12 untuk autentikasi berbasis token (Laravel Sanctum) dan CRUD data buku.',
    contact: new OA\Contact(name: 'Admin Perpustakaan', email: 'admin@example.com')
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'Server API')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token',
    description: 'Tempel token dari endpoint POST /api/login (tanpa kata "Bearer").'
)]
#[OA\Tag(name: 'Auth', description: 'Registrasi, login, profil, dan logout')]
#[OA\Tag(name: 'Books', description: 'CRUD data buku (wajib login)')]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The title field is required.'),
        new OA\Property(property: 'errors', type: 'object', example: ['title' => ['The title field is required.']]),
    ]
)]
#[OA\Schema(
    schema: 'MessageResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ]
)]
abstract class Controller
{
    //
}
