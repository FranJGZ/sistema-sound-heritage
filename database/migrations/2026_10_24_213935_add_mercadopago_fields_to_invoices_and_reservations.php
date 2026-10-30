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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('mp_order_id', 100)->nullable()->after('employee_id')->index();
            $table->string('mp_payment_id', 100)->nullable()->after('mp_order_id')->index();
            $table->string('payment_status', 50)->default('completed')->after('mp_payment_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('mp_order_id', 100)->nullable()->after('payment_status')->index();
            $table->string('mp_payment_id', 100)->nullable()->after('mp_order_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['mp_order_id']);
            $table->dropIndex(['mp_payment_id']);
            $table->dropColumn(['mp_order_id', 'mp_payment_id', 'payment_status']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['mp_order_id']);
            $table->dropIndex(['mp_payment_id']);
            $table->dropColumn(['mp_order_id', 'mp_payment_id']);
        });
    }
};
