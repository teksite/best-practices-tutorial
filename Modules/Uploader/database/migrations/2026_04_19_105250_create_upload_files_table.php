<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upload_files', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('original_name');
            $table->string('title')->nullable();
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->string('mime_type');
            $table->string('disk');
            $table->json('other')->nullable();
            $table->timestamps();

            $table->unique(['path', 'disk']);
            $table->index(['path', 'disk']);
        });


        Schema::create('upload_files_models', function (Blueprint $table) {
            $table->foreignUlid('upload_id')->constrained('upload_files', 'id')->cascadeOnDelete();
            $table->morphs('model');
            $table->string('name')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_files_models');
        Schema::dropIfExists('upload_files');
    }
};
