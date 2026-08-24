<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debtors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 11);
            $table->string('address')->nullable();
            $table->string('id_document_image')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'is_favorite']);
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debtors');
    }
};
