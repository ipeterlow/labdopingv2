<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Lista de todos los permisos del sistema basados en rutas
        $permissions = [
            // Recepción de Muestras (dopingsample)
            'dopingsample.index',
            'dopingsample.show',
            'dopingsample.create',
            'dopingsample.edit',
            'dopingsample.destroy',

            // Informes de Muestras (reportsample)
            'reportsample.index',
            'reportsample.show',
            'reportsample.create',
            'reportsample.edit',
            'reportsample.destroy',

            // Reporte de Muestras (sample)
            'sample.index',
            'sample.show',
            'sample.create',
            'sample.edit',
            'sample.destroy',

            // Libro Orina (bookurinesample)
            'bookurinesample.index',
            'bookurinesample.show',
            'bookurinesample.create',
            'bookurinesample.edit',
            'bookurinesample.destroy',

            // Libro Pelo (bookhairsample)
            'bookhairsample.index',
            'bookhairsample.show',
            'bookhairsample.create',
            'bookhairsample.edit',
            'bookhairsample.destroy',

            // Libro Saliva (booksalivasample)
            'booksalivasample.index',
            'booksalivasample.show',
            'booksalivasample.create',
            'booksalivasample.edit',
            'booksalivasample.destroy',

            // Empresas (company)
            'company.index',
            'company.show',
            'company.create',
            'company.edit',
            'company.destroy',

            // Usuarios (users)
            'users.index',
            'users.show',
            'users.create',
            'users.edit',
            'users.destroy',

            // Roles (roles)
            'roles.index',
            'roles.show',
            'roles.create',
            'roles.edit',
            'roles.destroy',

            // Permisos (permissions)
            'permissions.index',
            'permissions.show',
            'permissions.create',
            'permissions.edit',
            'permissions.destroy',
        ];

        // Crear todos los permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear rol Administrador con todos los permisos
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        // Asignar rol Administrador al usuario ID 3
        $user = User::find(3);
        if ($user) {
            $user->assignRole('Administrador');
            $this->command->info("Rol 'Administrador' asignado al usuario: {$user->email}");
        }

        $this->command->info('Permisos y roles creados exitosamente.');
        $this->command->info('Permisos creados: ' . count($permissions));
    }
}
