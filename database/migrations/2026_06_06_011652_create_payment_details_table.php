<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id(); $table->foreignId('invoice_id')->constrained('invoices'); $table->foreignId('payment_method_id')->constrained('payment_methods'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('payment_details'); }
};