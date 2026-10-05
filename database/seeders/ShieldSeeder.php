<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    /**
     * Recursos do painel que recebem permissões.
     *
     * @var array<int, string>
     */
    private array $recursos = [
        'Grupo', 'Cliente', 'Fornecedor', 'Vendedor', 'Produto', 'Movimentacao', 'Venda', 'Despesa', 'DespesaCategoria', 'Cidade',
    ];

    /**
     * Permissões do papel Vendedor: acompanhar suas vendas e consultar clientes/produtos.
     *
     * @var array<int, string>
     */
    private array $permissoesVendedor = [
        'ViewAny:Cliente',
        'View:Cliente',
        'ViewAny:Produto',
        'View:Produto',
        'ViewAny:Venda',
        'View:Venda',
        'Create:Venda',
        'Update:Venda',
        'Delete:Venda',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $habilidades = [
            'viewAny', 'view', 'create', 'update', 'delete', 'deleteAny',
            'restore', 'restoreAny', 'forceDelete', 'forceDeleteAny', 'reorder',
        ];

        foreach ($this->recursos as $recurso) {
            foreach ($habilidades as $habilidade) {
                Permission::findOrCreate(ucfirst($habilidade).":{$recurso}", 'web');
            }
        }

        $admin = Role::findOrCreate('Admin', 'web');
        $admin->syncPermissions(Permission::all());

        $vendedor = Role::findOrCreate('Vendedor', 'web');
        $vendedor->syncPermissions($this->permissoesVendedor);
    }
}
