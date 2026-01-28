<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            
            // Campos básicos del calendario
           $table->string('title');
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('color')->nullable();
            
            $table->string('codigo')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('factura')->nullable();
            $table->string('ordencompra')->nullable();
            $table->integer('cantidadFact')->default(0);
            
            // Campos específicos de tu formulario
            $table->string('op_number')->nullable();
            $table->string('cliente')->nullable();
            $table->integer('cantidad_req')->default(0);
            $table->date('fecha_entrega')->nullable();
            $table->date('fecha_produccion')->nullable();
            $table->string('estado')->default('pendiente');
            
            // Guardar los códigos como JSON (simple)
            $table->json('codigos')->nullable();
            
            // Metadata
            $table->timestamps();
            
            // Índices básicos
            $table->index('op_number');
            $table->index('estado');
            $table->index('start');
        });
    }

    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
};