<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"Admin","guard_name":"web","permissions":["ViewAny:Grupo","View:Grupo","Create:Grupo","Update:Grupo","Delete:Grupo","DeleteAny:Grupo","Restore:Grupo","RestoreAny:Grupo","ForceDelete:Grupo","ForceDeleteAny:Grupo","Reorder:Grupo","ViewAny:Cliente","View:Cliente","Create:Cliente","Update:Cliente","Delete:Cliente","DeleteAny:Cliente","Restore:Cliente","RestoreAny:Cliente","ForceDelete:Cliente","ForceDeleteAny:Cliente","Reorder:Cliente","ViewAny:Fornecedor","View:Fornecedor","Create:Fornecedor","Update:Fornecedor","Delete:Fornecedor","DeleteAny:Fornecedor","Restore:Fornecedor","RestoreAny:Fornecedor","ForceDelete:Fornecedor","ForceDeleteAny:Fornecedor","Reorder:Fornecedor","ViewAny:Vendedor","View:Vendedor","Create:Vendedor","Update:Vendedor","Delete:Vendedor","DeleteAny:Vendedor","Restore:Vendedor","RestoreAny:Vendedor","ForceDelete:Vendedor","ForceDeleteAny:Vendedor","Reorder:Vendedor","ViewAny:Produto","View:Produto","Create:Produto","Update:Produto","Delete:Produto","DeleteAny:Produto","Restore:Produto","RestoreAny:Produto","ForceDelete:Produto","ForceDeleteAny:Produto","Reorder:Produto","ViewAny:Movimentacao","View:Movimentacao","Create:Movimentacao","Update:Movimentacao","Delete:Movimentacao","DeleteAny:Movimentacao","Restore:Movimentacao","RestoreAny:Movimentacao","ForceDelete:Movimentacao","ForceDeleteAny:Movimentacao","Reorder:Movimentacao","ViewAny:Venda","View:Venda","Create:Venda","Update:Venda","Delete:Venda","DeleteAny:Venda","Restore:Venda","RestoreAny:Venda","ForceDelete:Venda","ForceDeleteAny:Venda","Reorder:Venda","ViewAny:Despesa","View:Despesa","Create:Despesa","Update:Despesa","Delete:Despesa","DeleteAny:Despesa","Restore:Despesa","RestoreAny:Despesa","ForceDelete:Despesa","ForceDeleteAny:Despesa","Reorder:Despesa","ViewAny:DespesaCategoria","View:DespesaCategoria","Create:DespesaCategoria","Update:DespesaCategoria","Delete:DespesaCategoria","DeleteAny:DespesaCategoria","Restore:DespesaCategoria","RestoreAny:DespesaCategoria","ForceDelete:DespesaCategoria","ForceDeleteAny:DespesaCategoria","Reorder:DespesaCategoria","ViewAny:Cidade","View:Cidade","Create:Cidade","Update:Cidade","Delete:Cidade","DeleteAny:Cidade","Restore:Cidade","RestoreAny:Cidade","ForceDelete:Cidade","ForceDeleteAny:Cidade","Reorder:Cidade"]},{"name":"Vendedor","guard_name":"web","permissions":["ViewAny:Cliente","View:Cliente","ViewAny:Produto","View:Produto","ViewAny:Venda","View:Venda","Create:Venda","Update:Venda","Delete:Venda"]}]';
        $directPermissions = '{"110":{"name":"ViewAny:Role","guard_name":"web"},"111":{"name":"ViewAny:User","guard_name":"web"},"112":{"name":"View:Role","guard_name":"web"},"113":{"name":"Create:Role","guard_name":"web"},"114":{"name":"Update:Role","guard_name":"web"},"115":{"name":"Delete:Role","guard_name":"web"},"116":{"name":"DeleteAny:Role","guard_name":"web"},"117":{"name":"Restore:Role","guard_name":"web"},"118":{"name":"ForceDelete:Role","guard_name":"web"},"119":{"name":"ForceDeleteAny:Role","guard_name":"web"},"120":{"name":"RestoreAny:Role","guard_name":"web"},"121":{"name":"Replicate:Role","guard_name":"web"},"122":{"name":"Reorder:Role","guard_name":"web"},"123":{"name":"View:User","guard_name":"web"},"124":{"name":"Create:User","guard_name":"web"},"125":{"name":"Update:User","guard_name":"web"},"126":{"name":"Delete:User","guard_name":"web"},"127":{"name":"DeleteAny:User","guard_name":"web"},"128":{"name":"Restore:User","guard_name":"web"},"129":{"name":"ForceDelete:User","guard_name":"web"},"130":{"name":"ForceDeleteAny:User","guard_name":"web"},"131":{"name":"RestoreAny:User","guard_name":"web"},"132":{"name":"Replicate:User","guard_name":"web"},"133":{"name":"Reorder:User","guard_name":"web"}}';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
