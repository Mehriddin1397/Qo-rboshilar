<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Production QA audit: har bir boshqa factory o'z enum-based status
            // maydonini aniq belgilaydi (Qorboshi/Blog/... => Draft) — bu yerda
            // avval 'role' umuman yo'q edi, faqat DB ustun default'iga tayanardi.
            // Natijada `User::factory()->create()` orqali olingan in-memory model
            // ->role null bo'lib qolardi (DB darajasida to'g'ri saqlangan bo'lsa
            // ham) — `actingAs()` bilan test qilinganda `$user->role->label()`
            // xato berardi (bugni topib tuzatdi).
            'role' => Role::User,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Admin,
        ]);
    }

    public function editor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Editor,
        ]);
    }
}
