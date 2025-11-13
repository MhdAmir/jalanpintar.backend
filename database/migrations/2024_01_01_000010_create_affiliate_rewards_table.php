<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('affiliate_rewards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('affiliate_code');
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('commission_value', 12, 2); // Percentage (e.g., 10.50) or Fixed amount
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->integer('total_referrals')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique per form
            $table->unique(['form_id', 'affiliate_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_rewards');
    }
};
