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
        Schema::create('upload_files', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('original_name');
            $table->string('title')->nullable();
            $table->string('path');
            $table->unsignedBigInteger('sizes');
            $table->string('mime_type');
            $table->string('disk');
            $table->timestamps();
        });


        Schema::create('upload_files_models', function (Blueprint $table) {
           $table->foreignId('upload_id')->references('id')->cascadeOnDelete();
           $table->morphs('model');


        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_files');
    }
};
