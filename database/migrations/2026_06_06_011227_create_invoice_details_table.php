<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id(); $table->integer('quantity'); $table->decimal('unit_price', 10, 2); $table->decimal('subtotal', 10, 2); $table->foreignId('invoice_id')->constrained('invoices'); $table->foreignId('product_id')->constrained('products'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('invoice_details'); }
};