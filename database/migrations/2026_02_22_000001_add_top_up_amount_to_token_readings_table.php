<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('token_readings', function (Blueprint $table) {
            $table->decimal('top_up_amount', 12, 2)->nullable()->after('token_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token_readings', function (Blueprint $table) {
            $table->dropColumn('top_up_amount');
        });
    }
};
