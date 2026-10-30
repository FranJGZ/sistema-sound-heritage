<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('deposit_amount', 12, 2)->nullable()->after('price');
            $table->string('payment_method')->nullable()->after('deposit_amount');
            $table->string('payment_status')->default('Pendiente de Seña')->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'payment_method', 'payment_status']);
        });
    }
};

