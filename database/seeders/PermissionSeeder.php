<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos el caché de Spatie (Muy importante para que no dé errores al resetear)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos de Administrador
        Permission::create(['name' => 'ver panel admin']);
        Permission::create(['name' => 'gestionar usuarios']);
        
        // Permisos de Usuario Normal
        Permission::create(['name' => 'abrir cajas']);
        Permission::create(['name' => 'recargar monedas']);
        Permission::create(['name' => 'comprar vip']);
    }
}