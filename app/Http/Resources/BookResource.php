<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Book',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Laskar Pelangi'),
        new OA\Property(property: 'author', type: 'string', example: 'Andrea Hirata'),
        new OA\Property(property: 'publisher', type: 'string', nullable: true, example: 'Bentang Pustaka'),
        new OA\Property(property: 'isbn', type: 'string', example: '9789793062792'),
        new OA\Property(property: 'published_year', type: 'integer', example: 2005),
        new OA\Property(property: 'stock', type: 'integer', example: 10),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Novel tentang anak-anak Belitung.'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'BookCollection',
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Book')),
        new OA\Property(
            property: 'links',
            type: 'object',
            properties: [
                new OA\Property(property: 'first', type: 'string', example: 'http://localhost:8000/api/books?page=1'),
                new OA\Property(property: 'last', type: 'string', example: 'http://localhost:8000/api/books?page=2'),
                new OA\Property(property: 'prev', type: 'string', nullable: true, example: null),
                new OA\Property(property: 'next', type: 'string', nullable: true, example: 'http://localhost:8000/api/books?page=2'),
            ]
        ),
        new OA\Property(
            property: 'meta',
            type: 'object',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'last_page', type: 'integer', example: 2),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 20),
            ]
        ),
    ]
)]
class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'published_year' => $this->published_year,
            'stock' => $this->stock,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
