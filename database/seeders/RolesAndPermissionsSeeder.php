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
            'user' => [
                'create', 'read', 'read.hidden', 'update', 'archived', 'destroy'
            ],
            'course' => [
                'create', 'read', 'read.hidden', 'update', 'archived', 'destroy'
            ],
            'course.setting' => [
                'create', 'read', 'read.hidden', 'update'
            ],
            'course.tutor' => [
                'owner.create', 'collaborator.invite','collaborator.archived', 'owner.change',  'owner.archived',
            ],
            'course.registration' => [
                'create', 'cancel', 'archived','destroy'
            ],
            'education' => [
                'create', 'read', 'read.hidden', 'update','archived','destroy'
            ],
            'profile' => [
                'create', 'read', 'read.hidden', 'update'
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
            'user.read',
            'education.read',
            'profile.create','profile.read', 'profile.update',
            'course.create', 'course.read', 'course.read.hidden', 'course.update', 'course.archived',
            'course.setting.read',
            'course.tutor.collaborator.invite', 'course.tutor.collaborator.archived',
            'course.registration.create','course.registration.cancel',
            
            
        ]);

        // ROL: Student (Solo puede ver contenido activo y público)
        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'user.read', 
            'education.read',
            'profile.create','profile.read', 'profile.update',
            'course.read',
            'course.registration.create', 'course.registration.cancel'
        ]);
    }
}
