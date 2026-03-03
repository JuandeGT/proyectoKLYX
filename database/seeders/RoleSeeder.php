<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos los roles
        $admin = Role::create(['name' => 'Admin']);
        $usuario = Role::create(['name' => 'Usuario']);

        // 2. Le damos TODOS los permisos al Admin
        $admin->givePermissionTo([
            'ver panel admin',
            'gestionar usuarios',
            'abrir cajas',
            'recargar monedas',
            'comprar vip'
        ]);

        // 3. Le damos solo los permisos básicos al Usuario
        $usuario->givePermissionTo([
            'abrir cajas',
            'recargar monedas',
            'comprar vip'
        ]);
    }
}