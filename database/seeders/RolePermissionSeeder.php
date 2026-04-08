<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-users',
            'show-users',
            'create-users',
            'edit-users',
            'download-users',
            'delete-users',
            'view-roles',
            'show-roles',
            'create-roles',
            'edit-roles',
            'download-roles',
            'delete-roles',
            'view-permissions',
            'show-permissions',
            'create-permissions',
            'edit-permissions',
            'download-permissions',
            'delete-permissions',
            'view-positions',
            'show-positions',
            'create-positions',
            'edit-positions',
            'delete-positions',
            'view-divisions',
            'show-divisions',
            'create-divisions',
            'edit-divisions',
            'delete-divisions',
            'view-employee-types',
            'show-employee-types',
            'create-employee-types',
            'edit-employee-types',
            'delete-employee-types',
            'view-employees',
            'show-employees',
            'create-employees',
            'edit-employees',
            'delete-employees',
            'view-orders',
            'show-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',
            // tasks
            'view-tasks',
            'show-tasks',
            'create-tasks',
            'edit-tasks',
            'delete-tasks',
            // task attachments
            'view-task-attachments',
            'show-task-attachments',
            'create-task-attachments',
            'edit-task-attachments',
            'delete-task-attachments',
            // task comments
            'view-task-comments',
            'show-task-comments',
            'create-task-comments',
            'edit-task-comments',
            'delete-task-comments',
            // order section permissions (driver vs managerial)
            'edit-order-report',
            'delete-order-report',
            'create-order-expenses',
            'delete-order-expenses',
            'create-order-etoll',
            'delete-order-etoll',
            'create-order-photos',
            'delete-order-photos',
            'view-units',
            'show-units',
            'create-units',
            'edit-units',
            'delete-units',
            // pools
            'view-pools',
            'show-pools',
            'create-pools',
            'edit-pools',
            'delete-pools',
            // absensi (status admin)
            'edit-absensi-status',
            // presensi views permissions
            'view-presensi',
            'view-presensi-all',
            // upload folders management
            'view-upload-folders',
            'delete-upload-folders',
            // absensi input permissions
            'create-absensi-masuk',
            'create-absensi-pulang',
            // order vehicle issues
            'view-order-vehicle-issues',
            'show-order-vehicle-issues',
            'create-order-vehicle-issues',
            'edit-order-vehicle-issues',
            'delete-order-vehicle-issues',
            // vehicle maintenances
            'view-vehicle-maintenances',
            'show-vehicle-maintenances',
            'create-vehicle-maintenances',
            'edit-vehicle-maintenances',
            'delete-vehicle-maintenances',
            // leaderboard / analytics
            'view-leaderboard',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $roles = [
            'Super Admin',
            'Admin IT',
            'Editor',
            'User',
            'Owner',
            'Direksi',
            'Managerial',
            'Administration',
            'Supervisor',
            'Coordinator',
            'Operational',
        ];

        $createdRoles = [];
        foreach ($roles as $roleName) {
            $createdRoles[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        $createdRoles['Super Admin']->permissions()->sync(Permission::all());
        $createdRoles['Admin IT']->permissions()->sync(Permission::all());

        $createdRoles['Editor']->permissions()->sync(
            Permission::whereIn('name', [
                'view-users', 'show-users',
                'view-roles', 'show-roles',
                'view-permissions', 'show-permissions',
                'view-positions', 'show-positions',
                'view-divisions', 'show-divisions',
                'view-employee-types', 'show-employee-types',
                'view-employees', 'show-employees',
                'view-orders', 'show-orders',
                'view-tasks', 'show-tasks',
                'view-vehicle-maintenances', 'show-vehicle-maintenances',
                'view-units', 'show-units',
                'view-pools', 'show-pools',
                'view-order-vehicle-issues', 'show-order-vehicle-issues',
                'view-leaderboard',
            ])->pluck('id')
        );

        // Managerial & Supervisor: full order management + vehicle issues management
        $createdRoles['Managerial']->permissions()->sync(
            Permission::whereIn('name', [
                'view-orders', 'show-orders', 'create-orders', 'edit-orders', 'delete-orders',
                'view-tasks', 'show-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks',
                'view-vehicle-maintenances', 'show-vehicle-maintenances', 'create-vehicle-maintenances', 'edit-vehicle-maintenances', 'delete-vehicle-maintenances',
                'view-task-attachments', 'show-task-attachments', 'create-task-attachments', 'edit-task-attachments', 'delete-task-attachments',
                'view-task-comments', 'show-task-comments', 'create-task-comments', 'edit-task-comments', 'delete-task-comments',
                'edit-order-report', 'delete-order-report',
                'create-order-expenses', 'delete-order-expenses',
                'create-order-etoll', 'delete-order-etoll',
                'create-order-photos', 'delete-order-photos',
                'view-pools', 'show-pools', 'create-pools', 'edit-pools', 'delete-pools',
                'edit-absensi-status',
                'view-presensi',
                'view-presensi-all',
                'view-upload-folders', 'delete-upload-folders',
                'create-absensi-masuk',
                'create-absensi-pulang',
                'view-order-vehicle-issues', 'show-order-vehicle-issues', 'create-order-vehicle-issues', 'edit-order-vehicle-issues', 'delete-order-vehicle-issues',
                'view-leaderboard',
            ])->pluck('id')
        );

        $createdRoles['Supervisor']->permissions()->sync(
            Permission::whereIn('name', [
                'view-orders', 'show-orders', 'create-orders', 'edit-orders',
                'view-tasks', 'show-tasks', 'create-tasks', 'edit-tasks',
                'view-vehicle-maintenances', 'show-vehicle-maintenances', 'create-vehicle-maintenances', 'edit-vehicle-maintenances',
                'view-task-attachments', 'show-task-attachments', 'create-task-attachments', 'edit-task-attachments', 'delete-task-attachments',
                'view-task-comments', 'show-task-comments', 'create-task-comments', 'edit-task-comments', 'delete-task-comments',
                'edit-order-report', 'delete-order-report',
                'create-order-expenses', 'delete-order-expenses',
                'create-order-etoll', 'delete-order-etoll',
                'create-order-photos', 'delete-order-photos',
                'view-pools', 'show-pools', 'create-pools', 'edit-pools', 'delete-pools',
                'edit-absensi-status',
                'view-presensi',
                'view-presensi-all',
                'view-upload-folders', 'delete-upload-folders',
                'create-absensi-masuk',
                'create-absensi-pulang',
                'view-order-vehicle-issues', 'show-order-vehicle-issues', 'create-order-vehicle-issues', 'edit-order-vehicle-issues',
                'view-leaderboard',
            ])->pluck('id')
        );

        // Operational (Driver): can access driver sections but cannot edit order basic
        $createdRoles['Operational']->permissions()->sync(
            Permission::whereIn('name', [
                'view-orders', 'show-orders',
                'view-tasks', 'show-tasks', 'create-tasks',
                'view-vehicle-maintenances', 'show-vehicle-maintenances', 'create-vehicle-maintenances',
                'create-task-attachments', 'delete-task-attachments',
                'create-task-comments', 'delete-task-comments',
                'edit-order-report',
                'view-presensi',
                'create-absensi-masuk',
                'create-absensi-pulang',
                'create-order-expenses', 'delete-order-expenses',
                'create-order-etoll', 'delete-order-etoll',
                'create-order-photos',
                'view-order-vehicle-issues', 'show-order-vehicle-issues', 'create-order-vehicle-issues',
                'view-leaderboard',
            ])->pluck('id')
        );

        $defaultUsers = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@royalapp.com',
                'password' => Hash::make('password'),
                'role' => 'Super Admin'
            ],
            [
                'name' => 'Admin IT',
                'email' => 'adminit@royalapp.com',
                'password' => Hash::make('password'),
                'role' => 'Admin IT'
            ],
            [
                'name' => 'Editor',
                'email' => 'editor@royalapp.com',
                'password' => Hash::make('password'),
                'role' => 'Editor'
            ],
            [
                'name' => 'User',
                'email' => 'user@royalapp.com',
                'password' => Hash::make('password'),
                'role' => 'User'
            ],
        ];

        foreach ($defaultUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );

            $user->roles()->sync([$createdRoles[$userData['role']]->id]);
        }
    }
}
