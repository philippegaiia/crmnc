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
        Schema::create('formula_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_ref_id')->constrained()->restrictOnDelete();
            $table->decimal('percentage', 7, 4)->nullable();
            $table->boolean('is_qsp')->default(false);
            $table->unsignedInteger('position')->nullable();
            $table->text('technical_comment')->nullable();
            $table->timestamps();

            $table->index(['formula_version_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_lines');
    }
};
