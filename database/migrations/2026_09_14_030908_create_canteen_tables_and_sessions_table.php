<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canteen_id')->constrained('canteens')->cascadeOnDelete();
            $table->string('table_number');
            $table->string('qr_code_token')->unique();
            $table->string('status')->default('available');
            $table->timestamps();

            $table->unique(['canteen_id', 'table_number']);
        });

        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->cascadeOnDelete();
            $table->string('session_token')->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('tables');
    }
};
