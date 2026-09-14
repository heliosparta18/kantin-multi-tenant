<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['id', 'tenant_id']);

            $table->foreign(['category_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
