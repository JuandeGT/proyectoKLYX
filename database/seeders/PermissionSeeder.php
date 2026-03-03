<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caché de Spatie (Muy importante para que no dé errores al resetear)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Permisos de Administrador
        Permission::create(['name' => 'ver panel admin']);
        Permission::create(['name' => 'gestionar usuarios']);
        
        // 3. Permisos de Usuario Normal
        Permission::create(['name' => 'abrir cajas']);
        Permission::create(['name' => 'recargar monedas']);
        Permission::create(['name' => 'comprar vip']);
    }
}