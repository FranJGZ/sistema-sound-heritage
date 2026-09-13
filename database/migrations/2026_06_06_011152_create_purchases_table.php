<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id(); $table->date('purchase_date'); $table->decimal('total', 10, 2); $table->foreignId('supplier_id')->constrained('suppliers'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('purchases'); }
};