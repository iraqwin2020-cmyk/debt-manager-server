<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debt_id')->constrained()->restrictOnDelete();
            $table->foreignId('debtor_id')->constrained()->restrictOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained('debt_installments')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('paid_at');
            $table->text('note')->nullable();
            $table->unsignedInteger('receipt_number');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'receipt_number']);
            $table->index(['tenant_id', 'debt_id']);
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
