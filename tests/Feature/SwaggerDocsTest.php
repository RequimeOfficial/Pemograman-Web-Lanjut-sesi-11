<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SwaggerDocsTest extends TestCase
{
    public function test_dokumentasi_openapi_bisa_digenerate_dan_lengkap(): void
    {
        Artisan::call('l5-swagger:generate');

        $docs = json_decode(file_get_contents(storage_path('api-docs/api-docs.json')), true);

        $this->assertSame('Perpustakaan API', $docs['info']['title']);
        $this->assertArrayHasKey('sanctum', $docs['components']['securitySchemes']);

        $operasi = [
            ['/api/register', 'post'], ['/api/login', 'post'], ['/api/me', 'get'], ['/api/logout', 'post'],
            ['/api/books', 'get'], ['/api/books', 'post'], ['/api/books/{book}', 'get'],
            ['/api/books/{book}', 'put'], ['/api/books/{book}', 'patch'], ['/api/books/{book}', 'delete'],
        ];

        foreach ($operasi as [$path, $method]) {
            $this->assertArrayHasKey($method, $docs['paths'][$path] ?? [], "$method $path belum terdokumentasi");
        }

        // Endpoint publik tanpa security, endpoint lain wajib token
        $this->assertArrayNotHasKey('security', $docs['paths']['/api/login']['post']);
        $this->assertSame([['sanctum' => []]], $docs['paths']['/api/books']['get']['security']);
    }

    public function test_halaman_swagger_ui_bisa_dibuka(): void
    {
        $this->get('/api/documentation')->assertOk()->assertSee('swagger-ui', false);
    }
}
