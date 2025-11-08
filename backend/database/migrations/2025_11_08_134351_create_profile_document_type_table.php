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
        Schema::create('profile_document_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0); // Para ordenar los documentos
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['profile_id', 'document_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_document_type');
    }
};
