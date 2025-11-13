<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->onDelete('cascade');
            $table->foreignUuid('submission_id')->nullable()->constrained('submissions')->onDelete('set null');
            $table->string('identifier'); // Submission number or custom identifier
            $table->string('name'); // Candidate name
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->json('result_data')->nullable(); // Rich result data
            $table->text('notes')->nullable();
            $table->timestamp('announced_at')->nullable();
            $table->timestamps();

            // Index for faster lookup
            $table->index(['form_id', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
