<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Laskar Pelangi',
            'author' => 'Andrea Hirata',
            'publisher' => 'Bentang Pustaka',
            'isbn' => '9789793062792',
            'published_year' => 2005,
            'stock' => 10,
            'description' => 'Novel tentang anak-anak Belitung.',
        ], $override);
    }

    public function test_semua_endpoint_buku_wajib_login(): void
    {
        $book = Book::factory()->create();

        $this->getJson('/api/books')->assertUnauthorized();
        $this->postJson('/api/books', $this->payload())->assertUnauthorized();
        $this->getJson("/api/books/{$book->id}")->assertUnauthorized();
        $this->putJson("/api/books/{$book->id}", ['stock' => 1])->assertUnauthorized();
        $this->deleteJson("/api/books/{$book->id}")->assertUnauthorized();

        $this->assertDatabaseCount('books', 1);
    }

    public function test_daftar_buku_berpaginasi(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Book::factory(15)->create();

        $this->getJson('/api/books?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonStructure(['data' => [['id', 'title', 'author', 'isbn', 'published_year', 'stock']], 'links', 'meta']);

        $this->getJson('/api/books?per_page=10&page=2')->assertOk()->assertJsonCount(5, 'data');
    }

    public function test_per_page_dibatasi_maksimal_100(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/books?per_page=5000')->assertOk()->assertJsonPath('meta.per_page', 100);
    }

    public function test_pencarian_berdasarkan_judul_atau_penulis(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Book::factory()->create(['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata']);
        Book::factory()->create(['title' => 'Sang Pemimpi', 'author' => 'Andrea Hirata']);
        Book::factory()->create(['title' => 'Bumi Manusia', 'author' => 'Pramoedya Ananta Toer']);

        $this->getJson('/api/books?search=hirata')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/books?search=bumi')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Bumi Manusia');
        $this->getJson('/api/books?search=tidak-ada')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_tambah_buku_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Laskar Pelangi')
            ->assertJsonPath('data.published_year', 2005);

        $this->assertDatabaseHas('books', ['isbn' => '9789793062792', 'stock' => 10]);
    }

    public function test_validasi_tambah_buku_422(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Book::factory()->create(['isbn' => '9789793062792']);

        $this->postJson('/api/books', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);

        $this->postJson('/api/books', ['published_year' => (int) date('Y') + 1, 'stock' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'author', 'isbn', 'published_year', 'stock']);

        $this->assertDatabaseCount('books', 1);
    }

    public function test_detail_buku_dan_404(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create(['title' => 'Bumi Manusia']);

        $this->getJson("/api/books/{$book->id}")->assertOk()->assertJsonPath('data.title', 'Bumi Manusia');
        $this->getJson('/api/books/99999')->assertNotFound()->assertJsonPath('message', 'Data tidak ditemukan.');
    }

    public function test_ubah_buku_put_dan_patch_sebagian(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create($this->payload());

        // PUT dengan ISBN miliknya sendiri tetap valid (aturan unique mengabaikan buku ini)
        $this->putJson("/api/books/{$book->id}", $this->payload(['title' => 'Laskar Pelangi (Revisi)']))
            ->assertOk()
            ->assertJsonPath('data.title', 'Laskar Pelangi (Revisi)');

        // PATCH hanya stok; field lain tidak berubah
        $this->patchJson("/api/books/{$book->id}", ['stock' => 3])
            ->assertOk()
            ->assertJsonPath('data.stock', 3)
            ->assertJsonPath('data.author', 'Andrea Hirata');
    }

    public function test_ubah_buku_dengan_isbn_milik_buku_lain_ditolak(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $buku = Book::factory()->create();
        $lain = Book::factory()->create();

        $this->patchJson("/api/books/{$buku->id}", ['isbn' => $lain->isbn])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);
    }

    public function test_hapus_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create();

        $this->deleteJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Buku berhasil dihapus');

        $this->assertModelMissing($book);
        $this->deleteJson("/api/books/{$book->id}")->assertNotFound();
    }
}
