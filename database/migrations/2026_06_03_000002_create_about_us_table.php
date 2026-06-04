<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('township')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('website')->nullable();
            $table->json('phone_numbers')->nullable();
            $table->json('email_addresses')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
