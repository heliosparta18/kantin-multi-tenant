<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->foreignId('canteen_id')->constrained('canteens')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('code'); // misal: ST01

            // Data rekening bank
            $table->string('bank_name')->nullable();
            $table->text('bank_account_number')->nullable(); // Disimpan terenkripsi
            $table->string('bank_account_last4', 4)->nullable(); // 4 digit terakhir untuk preview UI
            $table->string('bank_account_holder')->nullable();

            $table->string('status')->default('active');
            $table->timestamps();

            // Kunci unik komposit: Kode tenant yang sama dalam satu kantin DITOLAK
            $table->unique(['canteen_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
