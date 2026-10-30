<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('mp_refund_id')->nullable()->after('mp_payment_id');
            $table->timestamp('refunded_at')->nullable()->after('mp_refund_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('mp_refund_id')->nullable()->after('mp_payment_id');
            $table->timestamp('refunded_at')->nullable()->after('mp_refund_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('event_id');
            $table->string('payment_method')->nullable()->after('price');
            $table->string('payment_status')->default('completed')->after('payment_method');
            $table->string('mp_order_id')->nullable()->after('payment_status');
            $table->string('mp_payment_id')->nullable()->after('mp_order_id');
            $table->string('mp_refund_id')->nullable()->after('mp_payment_id');
            $table->timestamp('refunded_at')->nullable()->after('mp_refund_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['mp_refund_id', 'refunded_at']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['mp_refund_id', 'refunded_at']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'price',
                'payment_method',
                'payment_status',
                'mp_order_id',
                'mp_payment_id',
                'mp_refund_id',
                'refunded_at',
            ]);
        });
    }
};

