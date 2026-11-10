<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

/**
 * Semua field memakai 'sometimes' sehingga PATCH boleh mengirim sebagian field saja.
 */
#[OA\Schema(
    schema: 'BookUpdateRequest',
    description: 'Semua field opsional; kirim hanya field yang ingin diubah.',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Laskar Pelangi (Edisi Revisi)'),
        new OA\Property(property: 'author', type: 'string', maxLength: 255, example: 'Andrea Hirata'),
        new OA\Property(property: 'publisher', type: 'string', nullable: true, example: 'Bentang Pustaka'),
        new OA\Property(property: 'isbn', type: 'string', maxLength: 20, example: '9786020000011'),
        new OA\Property(property: 'published_year', type: 'integer', minimum: 1000, example: 2008),
        new OA\Property(property: 'stock', type: 'integer', minimum: 0, example: 15),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Edisi revisi dengan sampul baru.'),
    ]
)]
class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'publisher' => ['sometimes', 'nullable', 'string', 'max:255'],
            'isbn' => [
                'sometimes', 'required', 'string', 'max:20',
                // ISBN unik, kecuali milik buku yang sedang diubah
                Rule::unique('books', 'isbn')->ignore($this->route('book')),
            ],
            'published_year' => ['sometimes', 'required', 'integer', 'min:1000', 'max:'.date('Y')],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
