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
        Schema::create('media', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('original_name');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('path');
            $table->string('mime_type');
            $table->string('extension');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('disk');
            $table->timestamps();
            $table->unique(['disk', 'path']);
            $table->index(['disk', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
