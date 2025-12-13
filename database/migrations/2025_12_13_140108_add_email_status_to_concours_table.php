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
        Schema::table('concours', function (Blueprint $table) {
            $table->boolean('supervisors_notified')->default(false)->after('status');
            $table->boolean('candidats_notified')->default(false)->after('supervisors_notified');
            $table->timestamp('supervisors_notified_at')->nullable()->after('candidats_notified');
            $table->timestamp('candidats_notified_at')->nullable()->after('supervisors_notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concours', function (Blueprint $table) {
            $table->dropColumn(['supervisors_notified', 'candidats_notified', 'supervisors_notified_at', 'candidats_notified_at']);
        });
    }
};
