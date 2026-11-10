<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * 1 akun demo + 20 buku.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Perpustakaan',
                'password' => 'password', // otomatis di-hash oleh cast 'hashed'
                'email_verified_at' => now(),
            ],
        );

        $this->call(BookSeeder::class);
    }
}
