<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canteen_id')->constrained('canteens')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('idempotency_key')->unique();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('menu_id');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign(['menu_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('menus')
                ->cascadeOnDelete();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('payment_method')->default('qris');
            $table->string('payment_status')->default('pending');
            $table->decimal('amount', 12, 2);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
