<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Budi Santoso'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'rahasia123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'rahasia123'),
    ]
)]
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
