<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            // Latar visual (App\Enums\SceneSetting). Bukan kandungan fakta,
            // jadi tidak melalui aliran semakan.
            $table->string('setting', 30)->default('gurun_malam')->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn('setting');
        });
    }
};
