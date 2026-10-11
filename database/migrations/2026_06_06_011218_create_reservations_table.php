<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id(); $table->string('type'); $table->date('date'); $table->string('status'); $table->foreignId('customer_id')->constrained('customers'); $table->foreignId('venue_id')->constrained('venues'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('reservations'); }
};