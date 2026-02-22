<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nombre de usuario único para mostrar en la app (ej: @juan_perez)
            $table->string('username', 50)->nullable()->unique()->after('name');

            // Ruta relativa al avatar almacenado en storage/app/public/avatars/
            $table->string('avatar')->nullable()->after('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'avatar']);
        });
    }
};
