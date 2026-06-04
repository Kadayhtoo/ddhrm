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
        Schema::create('estimate_items', function (Blueprint $table) {
            $table->id();
            $table->string('estimate_id');
            $table->foreign('estimate_id')->references('estimate_id')->on('estimates')->onDelete('cascade');
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('item_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_items');
    }
};
