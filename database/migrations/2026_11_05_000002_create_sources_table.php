<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rujukan: ayat Al-Quran, hadis, atau kitab sirah.
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);              // quran | hadis | kitab_sirah
            $table->string('title');                  // cth. "Sahih al-Bukhari"
            $table->string('reference')->nullable();  // cth. "Kitab Manaqib al-Ansar, no. ..."
            $table->string('narrator')->nullable();   // perawi
            $table->string('grade')->nullable();      // darjat hadis (sahih/hasan/da'if)
            $table->text('text_ar')->nullable();
            $table->text('text_ms')->nullable();
            $table->text('notes')->nullable();        // nota penyemak
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
