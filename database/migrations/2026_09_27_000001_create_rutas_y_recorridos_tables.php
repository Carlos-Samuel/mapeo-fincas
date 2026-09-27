<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Línea dibujada en el mapa: camino, cerca, canal, tubería...
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')->constrained('fincas')->cascadeOnDelete();
            $table->foreignId('tipo_id')->nullable()->constrained('tipos')->nullOnDelete();
            $table->string('nombre');
            $table->json('geometria');                        // GeoJSON LineString
            $table->decimal('longitud_m', 10, 1)->nullable(); // se calcula sola
            $table->longText('contenido')->nullable();
            $table->timestamps();
        });

        // Recorrido guiado: secuencia ordenada de puntos existentes de la finca.
        Schema::create('recorridos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')->constrained('fincas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('color', 9)->default('#e91e63');
            $table->longText('contenido')->nullable();
            $table->timestamps();
        });

        Schema::create('recorrido_paradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recorrido_id')->constrained('recorridos')->cascadeOnDelete();
            $table->foreignId('punto_id')->constrained('puntos')->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->text('nota')->nullable();                 // qué decir/mostrar en esta parada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recorrido_paradas');
        Schema::dropIfExists('recorridos');
        Schema::dropIfExists('rutas');
    }
};
