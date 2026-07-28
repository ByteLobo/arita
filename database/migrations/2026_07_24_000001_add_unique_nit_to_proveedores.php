<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE UNIQUE INDEX proveedores_nit_ci_lower_unique ON proveedores (LOWER(nit_ci)) WHERE nit_ci IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS proveedores_nit_ci_lower_unique');
    }
};
