<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creditor_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('currency', ['IQD', 'USD']);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('payment_type', ['lump_sum', 'installments']);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'creditor_id']);
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_debts');
    }
};
