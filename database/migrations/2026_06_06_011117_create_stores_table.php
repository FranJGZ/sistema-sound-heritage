<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('stores', function (Blueprint $table) {
            $table->id(); $table->string('company_name'); $table->string('address'); $table->string('tax_condition'); $table->string('phone'); $table->string('tax_id'); $table->date('start_date'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('stores'); }
};