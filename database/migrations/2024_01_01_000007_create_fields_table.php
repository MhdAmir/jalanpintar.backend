<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->constrained('sections')->onDelete('cascade');
            $table->string('label');
            $table->string('name'); // Field name for form data
            $table->enum('type', ['text', 'email', 'phone', 'textarea', 'number', 'select', 'checkbox', 'radio', 'date', 'file', 'affiliate']);
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->json('options')->nullable(); // For select, checkbox, radio
            $table->json('validation_rules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
