<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpia la caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Lista de permisos por recurso
        $permissionsByResource = [
            'users' => [
                'create', 'read', 'read-hidden', 'update', 'delete', 'destroy'
            ],
            'courses' => [
                'create', 'read', 'read-hidden', 'update', 'delete', 'destroy', 'activate', 'assign-tutor'
            ],
            'modules' => [
                'create', 'read', 'read-hidden', 'update', 'delete', 'destroy', 'activate', 'update-order'
            ],
            'chapters' => [
                'create', 'read', 'read-hidden', 'update', 'delete', 'destroy', 'activate', 'update-order'
            ],
            'learning-contents' => [
                'create', 'read', 'read-hidden', 'update', 'delete', 'destroy', 'activate'
            ],
            'tutor-courses' => [
                'create', 'update', 'delete', 'activate', 'destroy'
            ],
            'registration' => [
                'create', 'cancel', 'delete','destroy'
            ],
        ];

        // 2. Genera y crea los permisos dinámicamente
        foreach ($permissionsByResource as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::create(['name' => "{$resource}.{$action}"]);
            }
        }

        // 3. Crea Roles y Asigna Permisos

        // ROL: Admin (Acceso total)
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // ROL: Tutor (Gestor de contenido, sin permisos destructivos ni de gestión de usuarios/tutores)
        $tutorRole = Role::create(['name' => 'tutor']);
        $tutorRole->givePermissionTo([
            'courses.create', 'courses.read', 'courses.read-hidden', 'courses.update', 'courses.delete', 'courses.activate',
            'modules.create', 'modules.read', 'modules.read-hidden', 'modules.update', 'modules.delete', 'modules.activate', 'modules.update-order',
            'chapters.create', 'chapters.read', 'chapters.read-hidden', 'chapters.update', 'chapters.delete', 'chapters.activate', 'chapters.update-order',
            'learning-contents.create', 'learning-contents.read', 'learning-contents.read-hidden', 'learning-contents.update', 'learning-contents.delete', 'learning-contents.activate',
            'registration.create',
        ]);

        // ROL: Student (Solo puede ver contenido activo y público)
        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'courses.read',
            'modules.read',
            'chapters.read',
            'learning-contents.read',
            'registration.create',
        ]);
    }
}
