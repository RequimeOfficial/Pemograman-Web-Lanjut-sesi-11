<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'isbn',
        'published_year',
        'stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'published_year' => 'integer',
            'stock' => 'integer',
        ];
    }
}
