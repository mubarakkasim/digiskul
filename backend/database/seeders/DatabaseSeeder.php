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
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'school_admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $bursarRole = Role::firstOrCreate(['name' => 'bursar']);
        $parentRole = Role::firstOrCreate(['name' => 'parent']);

        // Assign permissions to roles
        $adminRole->syncPermissions(Permission::all());
        $teacherRole->syncPermissions([
            'view students',
            'view attendance',
            'mark attendance',
            'view grades',
            'record grades',
            'view reports',
        ]);
        $bursarRole->syncPermissions([
            'view students',
            'view fees',
            'manage fees',
            'view payments',
            'record payments',
        ]);
        $parentRole->syncPermissions([
            'view students',
            'view attendance',
            'view grades',
            'view fees',
            'view payments',
        ]);

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@digiskul.app'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'active' => true,
            ]
        );
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        // Create sample school
        $school = School::firstOrCreate(
            ['subdomain' => 'nurlight'],
            [
                'name' => 'Nur Light Academy',
                'active' => true,
                'license_valid_until' => now()->addYear(),
            ]
        );

        // Create school admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'School Admin',
                'password' => Hash::make('password'),
                'role' => 'school_admin',
                'active' => true,
            ]
        );
        if (!$admin->hasRole('school_admin')) {
            $admin->assignRole($adminRole);
        }

        // Create sample teacher
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'John Teacher',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'active' => true,
            ]
        );
        if (!$teacher->hasRole('teacher')) {
            $teacher->assignRole($teacherRole);
        }
    }
}

