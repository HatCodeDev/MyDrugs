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
        Schema::create('repartidores', function (Blueprint $table) {
            $table->id();
            // Asumiendo que 'users' es la tabla de usuarios de Laravel/Shield
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('set null');
            $table->string('nombre_alias', 100)->unique();
            $table->string('vehiculo_descripcion', 255)->nullable();
            $table->string('zona_operativa_preferida', 255)->nullable();
            $table->boolean('disponible')->default(true);
            $table->decimal('calificacion_promedio', 3, 2)->nullable();
            $table->string('numero_contacto_cifrado', 255)->nullable(); // Simulación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repartidores');
    }
};