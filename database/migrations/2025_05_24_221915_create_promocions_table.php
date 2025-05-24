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
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_promocion', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->enum('tipo_descuento', ['PORCENTAJE', 'MONTO_FIJO']);
            $table->decimal('valor_descuento', 10, 2);
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->unsignedInteger('usos_maximos_global')->nullable();
            $table->unsignedInteger('usos_maximos_por_usuario')->nullable()->default(1);
            // 'usos_actuales' se podría calcular o manejar con una tabla pivote de usos
            $table->boolean('activo')->default(true);
            $table->foreignId('aplicable_a_categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->foreignId('aplicable_a_producto_id')->nullable()->constrained('productos')->onDelete('set null');
            $table->decimal('monto_minimo_pedido', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};