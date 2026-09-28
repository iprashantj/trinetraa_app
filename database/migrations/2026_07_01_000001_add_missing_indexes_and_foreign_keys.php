<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eye_checkup_bills', function (Blueprint $table) {
            $table->index('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

        Schema::table('product_enquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('eyewear_id')->nullable()->change();
            $table->foreign('eyewear_id')->references('id')->on('eyewears')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_enquiries', function (Blueprint $table) {
            $table->dropForeign(['eyewear_id']);
        });

        Schema::table('eye_checkup_bills', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id']);
        });
    }
};
