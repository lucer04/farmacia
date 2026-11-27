<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name', 150)->change();
        });
    }

    public function down(): void
    {
        $lengthFunction = DB::connection()->getDriverName() === 'sqlite' ? 'LENGTH' : 'CHAR_LENGTH';

        if (DB::table('customers')->whereRaw("{$lengthFunction}(name) > 50")->exists()) {
            throw new RuntimeException('No se puede reducir el nombre de clientes: hay valores de más de 50 caracteres.');
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->string('name', 50)->change();
        });
    }
};
