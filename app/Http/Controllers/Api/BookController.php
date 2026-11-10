<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    #[OA\Get(
        path: '/api/books',
        operationId: 'booksIndex',
        summary: 'Daftar buku (paginasi + pencarian)',
        security: [['sanctum' => []]],
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Cari berdasarkan judul atau penulis', schema: new OA\Schema(type: 'string', example: 'hirata')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman (1–100)', schema: new OA\Schema(type: 'integer', default: 10, maximum: 100, minimum: 1)),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar buku', content: new OA\JsonContent(ref: '#/components/schemas/BookCollection')),
            new OA\Response(response: 401, description: 'Belum login', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 10), 1), 100);
        $search = $request->string('search')->trim()->toString();

        $books = Book::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return BookResource::collection($books);
    }

    #[OA\Post(
        path: '/api/books',
        operationId: 'booksStore',
        summary: 'Tambah buku baru',
        security: [['sanctum' => []]],
        tags: ['Books'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BookRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Buku berhasil dibuat', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Book')])),
            new OA\Response(response: 401, description: 'Belum login', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal (mis. ISBN sudah dipakai)', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        return (new BookResource($book))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/books/{book}',
        operationId: 'booksShow',
        summary: 'Detail buku',
        security: [['sanctum' => []]],
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'book', in: 'path', required: true, description: 'ID buku', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail buku', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Book')])),
            new OA\Response(response: 401, description: 'Belum login', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 404, description: 'Buku tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['message' => 'Data tidak ditemukan.'])),
        ]
    )]
    public function show(Book $book): BookResource
    {
        return new BookResource($book);
    }

    #[OA\Put(
        path: '/api/books/{book}',
        operationId: 'booksUpdate',
        summary: 'Ubah buku',
        description: 'Field yang tidak dikirim tidak diubah.',
        security: [['sanctum' => []]],
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'book', in: 'path', required: true, description: 'ID buku', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BookUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Buku berhasil diubah', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Book')])),
            new OA\Response(response: 401, description: 'Belum login', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 404, description: 'Buku tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    #[OA\Patch(
        path: '/api/books/{book}',
        operationId: 'booksPatch',
        summary: 'Ubah sebagian field buku',
        security: [['sanctum' => []]],
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'book', in: 'path', required: true, description: 'ID buku', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BookUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Buku berhasil diubah', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Book')])),
            new OA\Response(response: 401, description: 'Belum login', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 404, description: 'Buku tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $book->update($request->validated());

        return new BookResource($book);
    }

    #[OA\Delete(
        path: '/api/books/{book}',
        operationId: 'booksDestroy',
        summary: 'Hapus buku',
        security: [['sanctum' => []]],
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'book', in: 'path', required: true, description: 'ID buku', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Buku berhasil dihapus', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['message' => 'Buku berhasil dihapus'])),
            new OA\Response(response: 401, description: 'Belum login', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 404, description: 'Buku tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
