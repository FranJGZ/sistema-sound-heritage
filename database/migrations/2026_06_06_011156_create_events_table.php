<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->date('date'); $table->string('type'); $table->integer('capacity'); $table->foreignId('venue_id')->constrained('venues'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('events'); }
};