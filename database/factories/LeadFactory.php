<?php

namespace Database\Factories;

use App\Enums\LeadPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            // 80% chance of having an email
            'email' => fake()->boolean(80) ? fake()->unique()->safeEmail() : null,
            // 80% chance of having a phone, ensuring we have some Iranian formats
            'phone' => fake()->boolean(80) ? (fake()->boolean(50) ? '0912'.fake()->randomNumber(7, true) : fake()->phoneNumber()) : null,
            'budget' => fake()->randomFloat(2, 5000, 150000),
            'source' => fake()->randomElement(['web', 'partner_api', 'manual', 'csv']),
            // Score and priority will be calculated by the seeder using the real engine
            'score' => 0,
            'priority' => LeadPriority::LOW->value,
            'additional_data' => fake()->boolean(30) ? ['company_size' => fake()->numberBetween(1, 500)] : null,
        ];
    }
}
