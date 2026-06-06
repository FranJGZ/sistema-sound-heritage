<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); $table->string('document_number'); $table->string('last_name'); $table->string('first_name'); $table->string('address'); $table->string('phone'); $table->foreignId('city_id')->constrained('cities'); $table->foreignId('employee_category_id')->constrained('employee_categories'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('employees'); }
};