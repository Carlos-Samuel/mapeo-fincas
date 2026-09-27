<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo: Maíz, Café, Casa, Pozo... Define ícono, color y capa del mapa.
        Schema::create('tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('aplica_a', 10)->default('ambos'); // lote | punto | ambos
            $table->string('icono')->nullable();              // ruta en el disco "uploads"
            $table->string('color', 9)->default('#4caf50');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('fincas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('ubicacion')->nullable();
            $table->json('geometria')->nullable();            // GeoJSON Polygon del contorno
            $table->decimal('area_ha', 10, 2)->nullable();
            $table->longText('contenido')->nullable();        // HTML del editor
            $table->timestamps();
        });

        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')->constrained('fincas')->cascadeOnDelete();
            $table->foreignId('tipo_id')->nullable()->constrained('tipos')->nullOnDelete();
            $table->string('codigo', 30)->nullable();
            $table->string('nombre');
            $table->json('geometria');                        // GeoJSON Polygon
            $table->decimal('area_ha', 10, 2)->nullable();
            $table->longText('contenido')->nullable();
            $table->timestamps();
        });

        Schema::create('puntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')->constrained('fincas')->cascadeOnDelete();
            $table->foreignId('tipo_id')->nullable()->constrained('tipos')->nullOnDelete();
            $table->string('nombre');
            $table->json('geometria');                        // GeoJSON Point
            $table->decimal('latitud', 10, 7)->nullable();    // se llenan solas desde geometria
            $table->decimal('longitud', 10, 7)->nullable();
            $table->longText('contenido')->nullable();
            $table->timestamps();
        });

        // Galería compartida por fincas, lotes y puntos (relación polimórfica).
        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable');
            $table->string('ruta');                           // ruta en el disco "uploads"
            $table->string('descripcion')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes');
        Schema::dropIfExists('puntos');
        Schema::dropIfExists('lotes');
        Schema::dropIfExists('fincas');
        Schema::dropIfExists('tipos');
    }
};
