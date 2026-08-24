<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debtor_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('currency', ['IQD', 'USD']);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('payment_type', ['lump_sum', 'installments']);
            $table->unsignedInteger('receipt_number');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'receipt_number']);
            $table->index(['tenant_id', 'debtor_id']);
            $table->index(['tenant_id', 'due_date']);
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
