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
        // Laravel pluraliza Stock a 'stocks', lo cual es correcto.
        // Si prefieres 'stock' como nombre de tabla, deberás especificarlo en el modelo.
        // Aquí usaré 'stocks' para seguir la convención.
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->unsignedInteger('cantidad_disponible')->default(0);
            $table->string('lote_numero', 100)->nullable();
            $table->date('fecha_caducidad')->nullable();
            $table->string('ubicacion_almacen', 100)->nullable();
            $table->timestamp('ultima_actualizacion_stock')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();

            // Si un producto solo puede tener una entrada de stock (sin lotes/ubicaciones múltiples)
            // $table->unique('producto_id'); 
            // Pero para lotes, esta restricción no aplica.
            $table->index(['producto_id', 'lote_numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};