<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('redeemed_by_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();
            $table->enum('status', ['unused', 'used', 'expired', 'cancelled'])->default('unused');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['assigned_tenant_id', 'status']);
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_codes');
    }
};
