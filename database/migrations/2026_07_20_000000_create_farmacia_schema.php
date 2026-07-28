<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre', 50)->unique();
            $table->timestampsTz();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre', 120);
            $table->string('email');
            $table->string('password');
            $table->foreignId('id_rol')->constrained('roles', 'id_rol')->restrictOnDelete();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestampsTz();
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();
        });

        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id('id_medicamento');
            $table->string('nombre', 160);
            $table->decimal('precio_compra', 12, 2);
            $table->decimal('precio_venta', 12, 2);
            $table->integer('stock')->default(0);
            $table->date('fecha_vencimiento');
            $table->foreignId('id_categoria')->constrained('categorias', 'id_categoria')->restrictOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();

        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('id_proveedor');
            $table->string('nombre_proveedor', 160);
            $table->string('telefono', 30)->nullable();
            $table->string('nit_ci', 40)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();
        });

        Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->foreignId('id_proveedor')->constrained('proveedores', 'id_proveedor')->restrictOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')->restrictOnDelete();
            $table->timestampTz('fecha')->useCurrent();
            $table->decimal('total', 12, 2)->default(0);
            $table->timestampsTz();

        });

        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id('id_detalle_compra');
            $table->foreignId('id_compra')->constrained('compras', 'id_compra')->restrictOnDelete();
            $table->foreignId('id_medicamento')->constrained('medicamentos', 'id_medicamento')->restrictOnDelete();
            $table->integer('cantidad');
            $table->decimal('precio_compra', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestampsTz();

            $table->unique(['id_compra', 'id_medicamento'], 'detalle_compras_compra_medicamento_unique');
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')->restrictOnDelete();
            $table->timestampTz('fecha')->useCurrent();
            $table->decimal('total', 12, 2)->default(0);
            $table->timestampsTz();

        });

        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id('id_detalle_venta');
            $table->foreignId('id_venta')->constrained('ventas', 'id_venta')->restrictOnDelete();
            $table->foreignId('id_medicamento')->constrained('medicamentos', 'id_medicamento')->restrictOnDelete();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestampsTz();

            $table->unique(['id_venta', 'id_medicamento'], 'detalle_ventas_venta_medicamento_unique');
        });

        DB::statement('CREATE UNIQUE INDEX usuarios_email_lower_unique ON usuarios (LOWER(email))');
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.unaccent_immutable(text)
            RETURNS text
            LANGUAGE sql
            IMMUTABLE
            PARALLEL SAFE
            AS $$ SELECT public.unaccent('public.unaccent', $1) $$
        SQL);
        DB::statement('CREATE UNIQUE INDEX categorias_nombre_lower_unique ON categorias (LOWER(public.unaccent_immutable(nombre)))');
        DB::statement('CREATE UNIQUE INDEX medicamentos_nombre_lower_unique ON medicamentos (LOWER(public.unaccent_immutable(nombre)))');

        DB::statement('ALTER TABLE medicamentos ADD CONSTRAINT medicamentos_stock_no_negativo CHECK (stock >= 0)');
        DB::statement('ALTER TABLE medicamentos ADD CONSTRAINT medicamentos_precio_compra_no_negativo CHECK (precio_compra >= 0)');
        DB::statement('ALTER TABLE medicamentos ADD CONSTRAINT medicamentos_precio_venta_no_negativo CHECK (precio_venta >= 0)');
        DB::statement('ALTER TABLE compras ADD CONSTRAINT compras_total_no_negativo CHECK (total >= 0)');
        DB::statement('ALTER TABLE detalle_compras ADD CONSTRAINT detalle_compras_cantidad_positiva CHECK (cantidad > 0)');
        DB::statement('ALTER TABLE detalle_compras ADD CONSTRAINT detalle_compras_precio_no_negativo CHECK (precio_compra >= 0)');
        DB::statement('ALTER TABLE detalle_compras ADD CONSTRAINT detalle_compras_subtotal_no_negativo CHECK (subtotal >= 0)');
        DB::statement('ALTER TABLE detalle_compras ADD CONSTRAINT detalle_compras_subtotal_consistente CHECK (subtotal = ROUND(cantidad * precio_compra, 2))');
        DB::statement('ALTER TABLE ventas ADD CONSTRAINT ventas_total_no_negativo CHECK (total >= 0)');
        DB::statement('ALTER TABLE detalle_ventas ADD CONSTRAINT detalle_ventas_cantidad_positiva CHECK (cantidad > 0)');
        DB::statement('ALTER TABLE detalle_ventas ADD CONSTRAINT detalle_ventas_precio_no_negativo CHECK (precio_unitario >= 0)');
        DB::statement('ALTER TABLE detalle_ventas ADD CONSTRAINT detalle_ventas_subtotal_no_negativo CHECK (subtotal >= 0)');
        DB::statement('ALTER TABLE detalle_ventas ADD CONSTRAINT detalle_ventas_subtotal_consistente CHECK (subtotal = ROUND(cantidad * precio_unitario, 2))');
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('detalle_compras');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('medicamentos');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
        DB::statement('DROP FUNCTION IF EXISTS public.unaccent_immutable(text)');
    }
};
