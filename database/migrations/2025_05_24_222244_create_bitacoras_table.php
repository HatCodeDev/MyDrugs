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
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('accion', 255);
            $table->text('descripcion_detallada')->nullable(); // Puede ser JSON
            $table->string('referencia_entidad', 100)->nullable(); // Ej: Producto, Pedido
            $table->unsignedBigInteger('referencia_id')->nullable(); // ID de la entidad referenciada
            $table->timestamp('fecha_evento')->useCurrent();
            $table->timestamps(); // created_at, updated_at

            $table->index(['referencia_entidad', 'referencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};