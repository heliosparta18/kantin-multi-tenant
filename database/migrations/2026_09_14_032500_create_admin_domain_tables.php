<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenants', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('tenants', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (! Schema::hasTable('user_canteen_roles')) {
            Schema::create('user_canteen_roles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('canteen_id')->constrained('canteens')->cascadeOnDelete();
                $table->string('role', 30); // owner|manager|finance|viewer
                $table->timestamps();
                $table->unique(['user_id', 'canteen_id', 'role']);
            });
        }

        if (! Schema::hasTable('user_tenant_roles')) {
            Schema::create('user_tenant_roles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('role', 30); // owner|operator|cashier
                $table->timestamps();
                $table->unique(['user_id', 'tenant_id', 'role']);
            });
        }

        if (! Schema::hasTable('tenant_balances')) {
            Schema::create('tenant_balances', function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->primary();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->bigInteger('available_amount')->default(0);
                $table->bigInteger('held_amount')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('commission_schemes')) {
            Schema::create('commission_schemes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->decimal('commission_rate', 6, 4);
                $table->dateTime('valid_from');
                $table->dateTime('valid_to')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tenant_bank_accounts')) {
            Schema::create('tenant_bank_accounts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('bank_code', 20);
                $table->string('account_holder', 120);
                $table->string('account_last4', 4);
                $table->text('account_number_cipher');
                $table->string('status', 20)->default('unverified');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('canteen_id')->nullable()->constrained('canteens')->nullOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->string('entity', 80);
                $table->string('entity_id', 64)->nullable();
                $table->string('action', 60);
                $table->string('request_id', 64)->nullable();
                $table->json('before')->nullable();
                $table->json('after')->nullable();
                $table->json('metadata')->nullable();
                $table->dateTime('logged_at');
                $table->timestamps();

                $table->index(['entity', 'entity_id']);
                $table->index(['tenant_id', 'action']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('tenant_bank_accounts');
        Schema::dropIfExists('commission_schemes');
        Schema::dropIfExists('tenant_balances');
        Schema::dropIfExists('user_tenant_roles');
        Schema::dropIfExists('user_canteen_roles');

        Schema::table('tenants', function (Blueprint $table): void {
            if (Schema::hasColumn('tenants', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('tenants', 'display_name')) {
                $table->dropColumn('display_name');
            }
        });
    }
};
