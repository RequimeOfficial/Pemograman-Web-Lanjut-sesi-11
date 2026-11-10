<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        operationId: 'authRegister',
        summary: 'Registrasi akun baru',
        description: 'Membuat akun dan langsung mengembalikan token Bearer.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Registrasi berhasil', content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal (mis. email sudah terdaftar)', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated()); // password di-hash oleh cast 'hashed'

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        operationId: 'authLogin',
        summary: 'Login dan dapatkan token',
        description: 'Dibatasi maksimal 6 percobaan per menit (throttle:6,1).',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Login berhasil', content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')),
            new OA\Response(response: 422, description: 'Email atau password salah', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 429, description: 'Terlalu banyak percobaan login', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        // Pesan disamakan untuk email tidak terdaftar & password salah agar tidak membocorkan email yang ada
        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken($request->input('device_name') ?: 'api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    #[OA\Get(
        path: '/api/me',
        operationId: 'authMe',
        summary: 'Profil user yang sedang login',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Data user', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])),
            new OA\Response(response: 401, description: 'Belum login / token tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    #[OA\Post(
        path: '/api/logout',
        operationId: 'authLogout',
        summary: 'Logout (hapus token yang sedang dipakai)',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logout berhasil', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['message' => 'Logout berhasil'])),
            new OA\Response(response: 401, description: 'Belum login / token tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        // Hanya token yang dipakai request ini yang dihapus; token di perangkat lain tetap aktif
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
