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
                'create', 'read', 'read-hidden', 'update', 'archived', 'destroy'
            ],
            'courses' => [
                'create', 'read', 'read-hidden', 'update', 'archived', 'destroy'
            ],
            'modules' => [
                'create', 'read', 'read-hidden', 'update', 'archived', 'destroy'
            ],
            'chapters' => [
                'create', 'read', 'read-hidden', 'update', 'archived', 'destroy',
            ],
            'learning-contents' => [
                'create', 'read', 'read-hidden', 'update', 'archived', 'destroy',
            ],
            'tutor-courses' => [
                'create-owner', 'invite-collaborator','archived-collaborator', 'change-owner',  'archived-owner',
            ],
            'registration' => [
                'create', 'cancel', 'archived','destroy'
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
            
            'courses.create', 'courses.read', 'courses.read-hidden', 'courses.update', 'courses.archived',
            'modules.create', 'modules.read', 'modules.read-hidden', 'modules.update', 'modules.archived',
            'chapters.create', 'chapters.read', 'chapters.read-hidden', 'chapters.update', 'chapters.archived',
            'learning-contents.create', 'learning-contents.read', 'learning-contents.read-hidden', 'learning-contents.update', 'learning-contents.archived',
            'registration.create','registration.cancel',
            'tutor-courses.invite-collaborator', 'tutor-courses.archived-collaborator',
        ]);

        // ROL: Student (Solo puede ver contenido activo y público)
        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'courses.read',
            'modules.read',
            'chapters.read',
            'learning-contents.read',
            'registration.create', 'registration.cancel'
        ]);
    }
}
