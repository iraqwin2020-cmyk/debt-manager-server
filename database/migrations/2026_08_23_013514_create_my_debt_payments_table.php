<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('my_debt_id')->constrained()->restrictOnDelete();
            $table->foreignId('creditor_id')->constrained()->restrictOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained('my_debt_installments')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('paid_at');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'my_debt_id']);
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_debt_payments');
    }
};
