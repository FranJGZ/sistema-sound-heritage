<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); $table->string('document_number'); $table->string('last_name'); $table->string('first_name'); $table->string('address'); $table->string('phone'); $table->string('tax_id'); $table->string('tax_condition'); $table->string('email'); $table->foreignId('city_id')->constrained('cities'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('customers'); }
};