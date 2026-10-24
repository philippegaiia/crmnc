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
        Schema::table('formula_lines', function (Blueprint $table) {
            $table->foreignId('lab_ingredient_id')
                ->nullable()
                ->after('ingredient_ref_id')
                ->constrained()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formula_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lab_ingredient_id');
        });
    }
};
