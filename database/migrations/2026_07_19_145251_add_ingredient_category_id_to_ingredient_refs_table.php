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
        Schema::table('ingredient_refs', function (Blueprint $table) {
            $table->foreignId('ingredient_category_id')->constrained()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredient_refs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ingredient_category_id');
        });
    }
};
