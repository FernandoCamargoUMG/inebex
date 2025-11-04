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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id');
            $table->foreignId('document_type_id')->constrained('document_types');
            $table->string('original_name');
            $table->string('path');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->enum('format', ['pdf'])->default('pdf');
            $table->foreignId('uploaded_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
