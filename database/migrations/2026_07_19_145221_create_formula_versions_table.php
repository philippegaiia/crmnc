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
        Schema::create('formula_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('workflow_status')->default('brouillon')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->text('version_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['formula_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_versions');
    }
};
