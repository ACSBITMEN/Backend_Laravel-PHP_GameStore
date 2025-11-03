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
        // Verificar que la tabla roles existe primero
        if (!Schema::hasTable('roles')) {
            throw new Exception('Roles table must be created first. Run roles migration.');
        }

        Schema::table('users', function (Blueprint $table) {
            // Eliminar el campo 'name' por defecto de Laravel
            $table->dropColumn('name');
            
            // Agregar nuevos campos personalizados
            $table->string('first_name', 100)->after('email');
            $table->string('last_name', 100)->after('first_name');
            $table->string('phone', 20)->nullable()->after('password');
            $table->string('country', 100)->nullable()->after('phone');
            $table->string('avatar', 255)->nullable()->after('country');
            $table->foreignId('role_id')->constrained('roles')->after('avatar');
            $table->boolean('status')->default(true)->after('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar la foreign key primero
            $table->dropForeign(['role_id']);
            
            // Eliminar campos personalizados
            $table->dropColumn([
                'first_name', 
                'last_name', 
                'phone', 
                'country', 
                'avatar', 
                'role_id', 
                'status'
            ]);
            
            // Restaurar el campo 'name' original
            $table->string('name')->after('id');
        });
    }
};