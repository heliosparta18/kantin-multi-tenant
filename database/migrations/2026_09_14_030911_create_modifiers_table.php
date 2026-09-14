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
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('menu_id');
            $table->string('name'); // misal: Pedas Level 1, Ekstra Keju
            $table->decimal('additional_price', 10, 2)->default(0.00);
            $table->timestamps();

            // Validasi database: modifier hanya bisa menunjuk menu milik tenant yang sama
            $table->foreign(['menu_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('menus')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifiers');
    }
};
