<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create(['name' => 'Admin']);
        $usuario = Role::create(['name' => 'Usuario']);

        $admin->givePermissionTo([
            'ver panel admin',
            'gestionar usuarios',
            'abrir cajas',
            'recargar monedas',
            'comprar vip'
        ]);

        $usuario->givePermissionTo([
            'abrir cajas',
            'recargar monedas',
            'comprar vip'
        ]);
    }
}