<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('event_sponsors', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_id')->constrained('events'); $table->foreignId('sponsor_id')->constrained('sponsors'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('event_sponsors'); }
};