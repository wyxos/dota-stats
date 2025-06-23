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
        Schema::table('dota_matches', function (Blueprint $table) {
            $table->json('details')->nullable();
            $table->boolean('is_parsed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dota_matches', function (Blueprint $table) {
            $table->dropColumn('details');
            $table->dropColumn('is_parsed');
        });
    }
};
