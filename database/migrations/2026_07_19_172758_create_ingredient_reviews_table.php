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
        Schema::create('ingredient_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_ingredient_id')->constrained()->restrictOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->index();
            $table->string('decision')->nullable()->index();
            $table->timestamp('decided_at')->nullable();
            $table->text('comment')->nullable();
            $table->json('submission_snapshot');
            $table->json('submitted_document_ids');
            $table->char('submitted_fingerprint', 64)->index();
            $table->date('valid_until')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_reviews');
    }
};
