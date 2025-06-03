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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Cliente
            $table->foreignId('repartidor_id')->nullable()->constrained('repartidores')->onDelete('set null');
            $table->foreignId('metodo_pago_id')->constrained('metodos_pago')->onDelete('restrict');
            $table->foreignId('promocion_id')->nullable()->constrained('promociones')->onDelete('set null');
            
            $table->text('direccion_entrega_cifrada')->nullable(); // Simulación
            $table->string('punto_entrega_especial', 255)->nullable();
            
            $table->decimal('subtotal_pedido', 10, 2)->nullable();
            $table->decimal('descuento_aplicado', 10, 2)->default(0.00);
            $table->decimal('total_pedido', 10, 2)->nullable();
            
            $table->string('estado_pedido', 50)->default('PENDIENTE'); // Ej: PENDIENTE, PROCESANDO, EN_RUTA, ENTREGADO, CANCELADO
            $table->timestamp('fecha_pedido')->useCurrent();
            $table->timestamp('fecha_estimada_entrega')->nullable();
            $table->text('notas_cliente')->nullable();
            $table->string('codigo_seguimiento', 100)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};