<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_membuat_akun_dan_mengembalikan_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Registrasi berhasil')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'budi@example.com')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'created_at']])
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_register_validasi_email_unik_dan_konfirmasi_password(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'beda12345',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_berhasil_mengembalikan_token_yang_bisa_dipakai(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com', 'password' => 'password']);

        $token = $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'password', 'device_name' => 'phpunit'])
            ->assertOk()
            ->assertJsonPath('message', 'Login berhasil')
            ->assertJsonPath('user.id', $user->id)
            ->json('token');

        $this->assertSame('phpunit', PersonalAccessToken::first()->name);

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@example.com');
    }

    public function test_login_password_salah_ditolak_422(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'password' => 'password']);

        $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'salah'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Email atau password salah.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_dibatasi_6_percobaan_per_menit(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'salah'])->assertUnprocessable();
        }

        $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'salah'])->assertTooManyRequests();
    }

    public function test_me_tanpa_token_ditolak_401(): void
    {
        $this->getJson('/api/me')->assertUnauthorized()->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_logout_menghapus_token_sehingga_tidak_bisa_dipakai_lagi(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;
        $tokenLain = $user->createToken('hp')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil');

        // Hanya token yang dipakai yang dihapus
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/books')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($tokenLain)->getJson('/api/books')->assertOk();
    }

    public function test_respons_tetap_json_walau_tanpa_header_accept(): void
    {
        $this->get('/api/me')
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json');
    }
}
