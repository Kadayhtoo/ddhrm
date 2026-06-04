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
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('invoice_id')->primary(); 
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('currency', 10)->default('MMK');
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('discount_value', 15, 2)->default(0.00);
            $table->enum('status', ['open', 'sent', 'paid', 'cancelled'])->default('open');
            $table->decimal('sub_total', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
