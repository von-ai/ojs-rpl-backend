<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan konfigurasi rekening dan pembiayaan APC ke tabel journals
        Schema::table('journals', function (Blueprint $table) {
            $table->boolean('apc_enabled')->default(false)->after('apc_fee');
            $table->decimal('apc_amount', 12, 2)->nullable()->after('apc_enabled');
            $table->string('payment_bank_name', 100)->nullable()->after('apc_amount');
            $table->string('payment_account_number', 50)->nullable()->after('payment_bank_name');
            $table->string('payment_account_holder', 191)->nullable()->after('payment_account_number');
            $table->text('payment_instructions')->nullable()->after('payment_account_holder');
        });

        // 2. Buat tabel article_payments untuk menampung tagihan & bukti pembayaran APC
        Schema::create('article_payments', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26)->index();
            $table->char('user_id', 26)->index();
            $table->string('invoice_number', 60)->unique();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->nullable();
            $table->string('status', 30)->default('pending_payment')->index(); // pending_payment, pending_verification, verified, rejected, waived
            $table->char('file_entry_id', 26)->nullable()->index();
            $table->string('sender_bank_name', 100)->nullable();
            $table->string('sender_account_name', 191)->nullable();
            $table->date('transfer_date')->nullable();
            $table->text('author_notes')->nullable();
            $table->text('verification_notes')->nullable();
            $table->char('verified_by', 26)->nullable()->index();
            $table->dateTime('verified_at', 6)->nullable();
            $table->dateTime('uploaded_at', 6)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('file_entry_id')->references('id')->on('files')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_payments');

        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn([
                'apc_enabled',
                'apc_amount',
                'payment_bank_name',
                'payment_account_number',
                'payment_account_holder',
                'payment_instructions',
            ]);
        });
    }
};
