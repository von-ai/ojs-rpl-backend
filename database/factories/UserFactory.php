<?php

namespace Database\Factories;

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
        $fullName = fake()->name();
        $username = fake()->unique()->userName();

        return [
            'email' => fake()->unique()->safeEmail(),
            'username' => $username,
            'full_name' => $fullName,
            'password' => static::$password ??= Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
            'locked_until' => null,
            'failed_login_count' => 0,
            'last_failed_login_at' => null,
            'version' => 1,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'status' => 'pending_verification',
        ]);
    }

    /**
     * Indicate that the model's account is locked.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'locked',
            'locked_until' => now()->addMinutes(15),
            'failed_login_count' => 5,
            'last_failed_login_at' => now(),
        ]);
    }

    /**
     * Indicate that the model's account is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disabled',
        ]);
    }
}
