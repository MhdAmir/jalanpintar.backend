<?php

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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignUuid('form_id')->constrained('forms')->onDelete('cascade');
            $table->foreignUuid('pricing_tier_id')->nullable()->constrained('pricing_tiers')->onDelete('set null');
            
            // Xendit Invoice Details
            $table->string('xendit_invoice_id')->unique();
            $table->string('xendit_invoice_url');
            $table->string('external_id')->unique();
            
            // Payment Details
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->enum('status', ['pending', 'paid', 'expired', 'failed'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_channel')->nullable();
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('xendit_invoice_id');
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
