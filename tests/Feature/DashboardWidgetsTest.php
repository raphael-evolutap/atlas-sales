<?php

use App\Enums\StatusVenda;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Vendas\VendaResource;
use App\Filament\Widgets\FaturamentoVendedor;
use App\Filament\Widgets\StatsResumo;
use App\Models\User;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Vendedor;
use Database\Seeders\ShieldSeeder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('dashboard carrega para admin', function () {
    $this->seed(ShieldSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('dashboard carrega para vendedor (sem widgets de despesas)', function () {
    $this->seed(ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Vendedor');
    Vendedor::factory()->for($user)->create();

    actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

it('faturamento por vendedor soma a comissão só das vendas fechadas', function () {
    $this->seed(ShieldSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $vendedor = Vendedor::factory()->create();

    $fechada = Venda::factory()->for($vendedor)->create(['status' => StatusVenda::Fechada]);
    VendaItem::factory()->for($fechada)->count(2)->create(['comissao_int' => 300]);
    $aberta = Venda::factory()->for($vendedor)->create(['status' => StatusVenda::Aberta]);
    VendaItem::factory()->for($aberta)->create(['comissao_int' => 999]);

    actingAs($admin);

    $registro = Livewire::test(FaturamentoVendedor::class)
        ->instance()
        ->getTableRecords()
        ->firstWhere('id', $vendedor->getKey());

    expect((int) $registro->comissao_int)->toBe(600);
});

/**
 * @return array<string, string>
 */
function statsResumo(array $pageFilters = []): array
{
    $widget = Livewire::test(StatsResumo::class, ['pageFilters' => $pageFilters])->instance();

    return collect((fn () => $this->getStats())->call($widget))
        ->mapWithKeys(fn (Stat $stat) => [(string) $stat->getLabel() => (string) $stat->getValue()])
        ->all();
}

it('resumo filtra vendas e comissão pelo período do dashboard', function () {
    $this->seed(ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Vendedor');
    $vendedor = Vendedor::factory()->for($user)->create();

    $dentro = Venda::factory()->for($vendedor)->create([
        'status' => StatusVenda::Fechada,
        'data_venda' => '2026-03-10',
        'valor_total_int' => 10000,
    ]);
    VendaItem::factory()->for($dentro)->create(['comissao_int' => 500]);

    $fora = Venda::factory()->for($vendedor)->create([
        'status' => StatusVenda::Fechada,
        'data_venda' => '2026-04-10',
        'valor_total_int' => 70000,
    ]);
    VendaItem::factory()->for($fora)->create(['comissao_int' => 3500]);

    $abertaNoPeriodo = Venda::factory()->for($vendedor)->create([
        'status' => StatusVenda::Aberta,
        'data_venda' => '2026-03-15',
        'valor_total_int' => 20000,
    ]);
    VendaItem::factory()->for($abertaNoPeriodo)->create(['comissao_int' => 1000]);

    actingAs($user);

    $stats = statsResumo(['startDate' => '2026-03-01', 'endDate' => '2026-03-31']);

    expect($stats['Vendas no período'])->toMatch('/R\$\s?100[.,]00/')
        ->and($stats['Comissão no período'])->toMatch('/R\$\s?5[.,]00/');
});

it('resumo sem filtro mostra o mês atual', function () {
    $this->seed(ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Vendedor');
    $vendedor = Vendedor::factory()->for($user)->create();

    Venda::factory()->for($vendedor)->create([
        'status' => StatusVenda::Fechada,
        'data_venda' => now()->startOfMonth(),
        'valor_total_int' => 4200,
    ]);
    Venda::factory()->for($vendedor)->create([
        'status' => StatusVenda::Fechada,
        'data_venda' => now()->subMonth()->startOfMonth(),
        'valor_total_int' => 99900,
    ]);

    actingAs($user);

    expect(statsResumo()['Vendas do mês'])->toMatch('/R\$\s?42[.,]00/');
});

it('admin vê todos os atalhos de criação no dashboard', function () {
    $this->seed(ShieldSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    actingAs($admin);

    Livewire::test(Dashboard::class)
        ->assertActionVisible('criarVendaResource')
        ->assertActionVisible('criarClienteResource')
        ->assertActionVisible('criarProdutoResource')
        ->assertActionVisible('criarMovimentacaoResource')
        ->assertActionVisible('criarDespesaResource')
        ->assertActionVisible('criarFornecedorResource')
        ->assertActionHasUrl('criarVendaResource', VendaResource::getUrl('create'));
});

it('vendedor vê só os atalhos de criação que tem permissão', function () {
    $this->seed(ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Vendedor');
    Vendedor::factory()->for($user)->create();

    actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertActionVisible('criarVendaResource')
        ->assertActionHidden('criarClienteResource')
        ->assertActionHidden('criarProdutoResource')
        ->assertActionHidden('criarDespesaResource')
        ->assertActionHidden('criarFornecedorResource');
});
