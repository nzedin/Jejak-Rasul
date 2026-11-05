<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pilihan watak rekaan (Yusuf). Hanya mengubah perjalanan watak rekaan,
        // tidak sekali-kali mengubah fakta sejarah.
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('prompt');
            $table->string('option_a');
            $table->string('option_b');
            $table->text('outcome_a');
            $table->text('outcome_b');
            $table->string('status', 20)->default('draf');
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // "Fakta atau Mitos?"
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->text('statement');
            $table->string('answer', 20); // fakta | mitos | lemah
            $table->text('explanation');
            $table->foreignId('source_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('status', 20)->default('draf');
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('choices');
    }
};
