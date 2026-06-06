<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); $table->date('registration_date'); $table->foreignId('customer_id')->constrained('customers'); $table->foreignId('event_id')->constrained('events'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('attendances'); }
};