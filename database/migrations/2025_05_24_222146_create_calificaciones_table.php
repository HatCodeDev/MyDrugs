<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Asumiendo tabla 'users'
            $table->tinyInteger('puntuacion')->unsigned(); // 1 a 5
            $table->text('comentario')->nullable();
            $table->timestamp('fecha_calificacion')->useCurrent();
            $table->timestamps();

            $table->unique(['producto_id', 'user_id']); // Un usuario solo puede calificar un producto una vez
        });

        // Para la restricción CHECK (puntuacion >= 1 AND puntuacion <= 5)
        // SQLite no soporta CHECK constraints agregadas con ALTER TABLE en versiones antiguas.
        // MySQL y PostgreSQL sí. Para mayor compatibilidad, esto se valida en la app.
        // Si usas MySQL >= 8.0.16 o PostgreSQL:
        // DB::statement('ALTER TABLE calificaciones ADD CONSTRAINT chk_puntuacion CHECK (puntuacion >= 1 AND puntuacion <= 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};