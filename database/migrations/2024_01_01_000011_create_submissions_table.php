<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('submission_number')->unique(); // e.g., SUB-2024-00001
            $table->string('name')->nullable(); // Contact name
            $table->string('email')->nullable(); // Contact email
            $table->string('phone')->nullable(); // Contact phone
            $table->json('data'); // All form field data

            // Payment related
            $table->foreignUuid('pricing_tier_id')->nullable()->constrained('pricing_tiers')->onDelete('set null');
            $table->json('selected_upsells')->nullable(); // Array of upsell IDs
            $table->decimal('tier_amount', 12, 2)->default(0);
            $table->decimal('upsells_amount', 12, 2)->default(0);
            $table->decimal('affiliate_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Affiliate related
            $table->foreignUuid('affiliate_reward_id')->nullable()->constrained('affiliate_rewards')->onDelete('set null');

            // Status
            $table->enum('status', allowed: ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
