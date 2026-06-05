<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'item_type')) {
                $table->string('item_type')->nullable()->after('total');
            }
            if (!Schema::hasColumn('invoice_items', 'description')) {
                $table->text('description')->nullable()->after('item_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('invoice_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });
    }
};
