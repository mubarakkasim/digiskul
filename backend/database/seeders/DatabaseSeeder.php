<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view students',
            'create students',
            'edit students',
            'delete students',
            'view attendance',
            'mark attendance',
            'view grades',
            'record grades',
            'view fees',
            'manage fees',
            'view payments',
            'record payments',
            'view reports',
            'generate reports',
            'manage archive',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $adminRole = Role::create(['name' => 'school_admin']);
        $teacherRole = Role::create(['name' => 'teacher']);
        $bursarRole = Role::create(['name' => 'bursar']);
        $parentRole = Role::create(['name' => 'parent']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $teacherRole->givePermissionTo([
            'view students',
            'view attendance',
            'mark attendance',
            'view grades',
            'record grades',
            'view reports',
        ]);
        $bursarRole->givePermissionTo([
            'view students',
            'view fees',
            'manage fees',
            'view payments',
            'record payments',
        ]);
        $parentRole->givePermissionTo([
            'view students',
            'view attendance',
            'view grades',
            'view fees',
            'view payments',
        ]);

        // Create super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@digiskul.app',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'active' => true,
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Create sample school
        $school = School::create([
            'name' => 'Nur Light Academy',
            'subdomain' => 'nurlight',
            'active' => true,
            'license_valid_until' => now()->addYear(),
        ]);

        // Create school admin
        $admin = User::create([
            'school_id' => $school->id,
            'name' => 'School Admin',
            'email' => 'admin@nurlight.digiskul.app',
            'password' => Hash::make('password'),
            'role' => 'school_admin',
            'active' => true,
        ]);
        $admin->assignRole($adminRole);

        // Create sample teacher
        $teacher = User::create([
            'school_id' => $school->id,
            'name' => 'John Teacher',
            'email' => 'teacher@nurlight.digiskul.app',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'active' => true,
        ]);
        $teacher->assignRole($teacherRole);
    }
}

