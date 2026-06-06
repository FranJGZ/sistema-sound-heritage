<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id(); $table->integer('quantity'); $table->decimal('price', 10, 2); $table->foreignId('purchase_id')->constrained('purchases'); $table->foreignId('product_id')->constrained('products'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('purchase_details'); }
};