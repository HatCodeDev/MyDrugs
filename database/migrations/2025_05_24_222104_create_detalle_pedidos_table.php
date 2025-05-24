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
        Schema::create('detalles_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict'); // O onDelete('set null') si se quiere mantener el detalle si el producto se borra
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario_en_pedido', 10, 2); // Precio al momento de la compra
            $table->decimal('subtotal', 10, 2); // cantidad * precio_unitario_en_pedido
            $table->timestamps();

            $table->unique(['pedido_id', 'producto_id']); // Evitar duplicados del mismo producto en el mismo pedido
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_pedido');
    }
};