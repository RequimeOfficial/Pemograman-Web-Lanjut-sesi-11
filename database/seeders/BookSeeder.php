<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

/**
 * 5 buku nyata (mudah dicari saat demo, mis. ?search=hirata) + 15 buku acak dari factory = 20 buku.
 */
class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            ['Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', '9789793062792', 2005, 10, 'Novel tentang perjuangan anak-anak Belitung bersekolah.'],
            ['Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', '9789793062921', 2006, 7, 'Lanjutan kisah Laskar Pelangi.'],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Lentera Dipantara', '9789799731234', 1980, 5, 'Kisah Minke pada masa kolonial Hindia Belanda.'],
            ['Ronggeng Dukuh Paruk', 'Ahmad Tohari', 'Gramedia Pustaka Utama', '9789792201963', 1982, 4, 'Kehidupan seorang ronggeng di desa kecil.'],
            ['Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', '9789792248616', 2009, 8, 'Kisah santri di sebuah pondok pesantren.'],
        ];

        foreach ($books as [$title, $author, $publisher, $isbn, $year, $stock, $description]) {
            Book::updateOrCreate(['isbn' => $isbn], [
                'title' => $title,
                'author' => $author,
                'publisher' => $publisher,
                'published_year' => $year,
                'stock' => $stock,
                'description' => $description,
            ]);
        }

        Book::factory(15)->create();
    }
}
