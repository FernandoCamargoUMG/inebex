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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onCascadeOndelete();
            $table->foreignId('appointment_type_id')->constrained('appointment_types');
            $table->string('title')->nullable();
            $table->dateTime('begin');
            $table->dateTime('end');
            $table->enum('status', ['pending', 'completed', 'canceled', 'confirmed'])->default('pending');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
