<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['clock_in', 'clock_out', 'status', 'full_day']);
            $table->date('requested_date');
            $table->dateTime('requested_clock_in_at')->nullable();
            $table->dateTime('requested_clock_out_at')->nullable();
            $table->enum('requested_status', ['present', 'absent', 'late', 'half_day', 'holiday', 'weekend', 'on_leave'])->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'requested_date']);
            $table->index(['status', 'requested_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_requests');
    }
};
