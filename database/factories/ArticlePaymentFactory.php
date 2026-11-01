<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ArticlePayment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticlePayment>
 */
class ArticlePaymentFactory extends Factory
{
    protected $model = ArticlePayment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'user_id' => User::factory(),
            'invoice_number' => 'INV-'.fake()->year().'-'.fake()->unique()->numerify('#####'),
            'amount' => 500000.00,
            'amount_paid' => null,
            'status' => 'pending_payment',
            'file_entry_id' => null,
            'sender_bank_name' => null,
            'sender_account_name' => null,
            'transfer_date' => null,
            'author_notes' => null,
            'verification_notes' => null,
            'verified_by' => null,
            'verified_at' => null,
            'uploaded_at' => null,
            'version' => 1,
        ];
    }

    public function pendingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_verification',
            'amount_paid' => 500000.00,
            'sender_bank_name' => 'Bank Mandiri',
            'sender_account_name' => fake()->name(),
            'transfer_date' => now()->toDateString(),
            'uploaded_at' => now(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'amount_paid' => 500000.00,
            'sender_bank_name' => 'Bank Sulselbar',
            'sender_account_name' => fake()->name(),
            'transfer_date' => now()->subDay()->toDateString(),
            'uploaded_at' => now()->subDay(),
            'verified_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'amount_paid' => 500000.00,
            'sender_bank_name' => 'BCA',
            'sender_account_name' => fake()->name(),
            'transfer_date' => now()->subDays(2)->toDateString(),
            'uploaded_at' => now()->subDays(2),
            'verification_notes' => 'Bukti transfer buram dan nominal tidak sesuai.',
        ]);
    }

    public function waived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waived',
            'verification_notes' => 'Diberikan pembebasan biaya publikasi oleh dewan redaksi.',
            'verified_at' => now(),
        ]);
    }
}
