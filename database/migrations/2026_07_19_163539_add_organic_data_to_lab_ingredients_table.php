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
        Schema::table('lab_ingredients', function (Blueprint $table) {
            $table->string('organic_certification_status')
                ->default('inconnu')
                ->after('grade');
            $table->decimal('organic_content_percentage', 5, 2)
                ->nullable()
                ->after('organic_certification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_ingredients', function (Blueprint $table) {
            $table->dropColumn([
                'organic_certification_status',
                'organic_content_percentage',
            ]);
        });
    }
};
