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
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('supervisors_notified')->default(false)->after('status');
            $table->boolean('students_notified')->default(false)->after('supervisors_notified');
            $table->timestamp('supervisors_notified_at')->nullable()->after('students_notified');
            $table->timestamp('students_notified_at')->nullable()->after('supervisors_notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['supervisors_notified', 'students_notified', 'supervisors_notified_at', 'students_notified_at']);
        });
    }
};
