<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('staff')->after('password');
            $table->unsignedBigInteger('department_id')->nullable()->after('role');
            $table->decimal('salary', 12, 2)->nullable()->after('department_id');
            $table->unsignedBigInteger('shift_id')->nullable()->after('salary');
            $table->boolean('is_active')->default(true)->after('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department_id', 'salary', 'shift_id', 'is_active']);
        });
    }
};
