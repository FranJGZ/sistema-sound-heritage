<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); 
            $table->string('type'); 
            $table->string('name'); 
            $table->decimal('price', 10, 2); 
            $table->timestamps();
            $table->integer('stock')->default(0); 
            $table->json('specs')->nullable();
        });
    }
    public function down() { 
        Schema::dropIfExists('products');
    }
};