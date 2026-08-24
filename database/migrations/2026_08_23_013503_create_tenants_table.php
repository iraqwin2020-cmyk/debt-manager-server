<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 11)->unique();
            $table->string('logo')->nullable();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->enum('status', ['active', 'trial', 'expired', 'suspended', 'cancelled'])->default('trial');
            $table->enum('type', ['online', 'offline'])->default('online');
            $table->enum('locale', ['ar', 'en', 'ku'])->default('ar');
            $table->enum('theme', ['light', 'dark'])->default('light');
            $table->unsignedInteger('rows_per_page')->default(20);
            $table->unsignedInteger('due_reminder_days')->default(3);
            $table->unsignedInteger('overdue_grace_days')->default(0);
            $table->unsignedInteger('debt_receipt_start_number')->default(1);
            $table->unsignedInteger('next_debt_receipt_number')->default(1);
            $table->unsignedInteger('payment_receipt_start_number')->default(1);
            $table->unsignedInteger('next_payment_receipt_number')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
