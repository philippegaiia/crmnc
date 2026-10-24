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
        Schema::create('ingredient_refs', function (Blueprint $table) {
            $table->id();
            $table->string('display_name')->index();
            $table->string('normalized_inci')->unique();
            $table->json('synonyms')->nullable();
            $table->string('origin');
            $table->string('label_position')->default('a_statuer')->index();
            $table->text('conditions')->nullable();
            $table->string('organic_eligibility')->default('inconnu');
            $table->string('status')->default('a_valider')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_refs');
    }
};
