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

        // Permisos agrupados por gestión
        $userPermissions = [
            'crear usuarios',
            'editar usuarios',
            'borrar usuarios',
            'ver usuarios',
        ];
        $coursePermissions = [
            'crear cursos',
            'editar cursos',
            'borrar cursos',
            'ver cursos',
            'activar cursos',
            'asignar tutor a cursos',
        ];
    
        // Unir todos los permisos
        $permissions = array_merge($coursePermissions,$userPermissions);

        // Crear los permisos
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // Rol de Admin: recibe todos los permisos
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo($permissions);

        // Rol Tutor: puede gestionar formularios y módulos, y cursos
        $tutorCoursePermissions = array_diff(
        $coursePermissions,
        ['borrar cursos', 'asignar tutor a cursos']
        );
        $tutorPermissions = array_merge($tutorCoursePermissions);
        $roleTutor = Role::create(['name' => 'tutor']);
        $roleTutor->givePermissionTo($tutorPermissions);

        // Rol Student: solo puede ver cursos
        $studentPermissions = ['ver cursos'];
        $roleStudent = Role::create(['name' => 'student']);
        $roleStudent->givePermissionTo($studentPermissions);

        // Ejemplo: puedes crear roles específicos para cada gestión
        // $roleCursos = Role::create(['name' => 'gestor_cursos']);
        // $roleCursos->givePermissionTo($coursePermissions);
        // $roleFormularios = Role::create(['name' => 'gestor_formularios']);
        // $roleFormularios->givePermissionTo($formPermissions);
    }
}
