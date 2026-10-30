<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('events', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->date('date'); 
            $table->time('time')->nullable(); 
            $table->string('type'); 
            $table->text('description')->nullable(); 
            $table->integer('capacity'); 
            $table->decimal('price', 10, 2)->default(0); 
            $table->foreignId('venue_id')->constrained('venues'); 
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('events'); }
};