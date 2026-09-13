<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id(); $table->string('invoice_type'); $table->string('point_of_sale'); $table->string('receipt_number'); $table->date('issue_date'); $table->date('due_date'); $table->decimal('total', 10, 2); $table->foreignId('store_id')->constrained('stores'); $table->foreignId('customer_id')->constrained('customers'); $table->foreignId('employee_id')->constrained('employees'); $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('invoices'); }
};