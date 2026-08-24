<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_debt_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('my_debt_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('seq_number');
            $table->unsignedBigInteger('amount');
            $table->date('due_date');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['my_debt_id', 'seq_number']);
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_debt_installments');
    }
};
