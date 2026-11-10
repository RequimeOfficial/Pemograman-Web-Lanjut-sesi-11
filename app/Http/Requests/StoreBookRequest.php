<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookRequest',
    required: ['title', 'author', 'isbn', 'published_year', 'stock'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Laskar Pelangi'),
        new OA\Property(property: 'author', type: 'string', maxLength: 255, example: 'Andrea Hirata'),
        new OA\Property(property: 'publisher', type: 'string', nullable: true, example: 'Bentang Pustaka'),
        new OA\Property(property: 'isbn', type: 'string', maxLength: 20, example: '9786020000011'),
        new OA\Property(property: 'published_year', type: 'integer', minimum: 1000, example: 2005),
        new OA\Property(property: 'stock', type: 'integer', minimum: 0, example: 10),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Novel tentang anak-anak Belitung.'),
    ]
)]
class StoreBookRequest extends FormRequest
{
    /**
     * Akses sudah dibatasi middleware auth:sanctum pada route.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:20', 'unique:books,isbn'],
            'published_year' => ['required', 'integer', 'min:1000', 'max:'.date('Y')],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}
