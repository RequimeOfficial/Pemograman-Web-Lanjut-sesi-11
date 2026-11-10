<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(3), '.'),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'isbn' => fake()->unique()->isbn13(),
            'published_year' => fake()->numberBetween(1990, (int) date('Y')),
            'stock' => fake()->numberBetween(0, 50),
            'description' => fake()->paragraph(),
        ];
    }
}
