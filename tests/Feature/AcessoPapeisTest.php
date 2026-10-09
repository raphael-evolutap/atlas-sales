<?php

use App\Enums\StatusVenda;
use App\Filament\Resources\Despesas\DespesaResource;
use App\Filament\Resources\Vendas\Pages\CreateVenda;
use App\Filament\Resources\Vendas\VendaResource;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Despesa;
use App\Models\Fornecedor;
use App\Models\Grupo;
use App\Models\Movimentacao;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use Database\Seeders\ShieldSeeder;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    // Apenas roles/permissions — sem dados de exemplo
    $this->seed(ShieldSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');

    $this->vendedorUser = User::factory()->create();
    $this->vendedorUser->assignRole('Vendedor');
    $this->vendedor = Vendedor::factory()->for($this->vendedorUser)->create();
});

it('admin tem acesso irrestrito', function () {
    expect($this->admin->hasRole('Admin'))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('viewAny', Venda::class))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('viewAny', Despesa::class))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('viewAny', Produto::class))->toBeTrue();
});

it('vendedor vê apenas suas vendas na listagem', function () {
    $outroVendedor = Vendedor::factory()->create();

    Venda::factory()->for($this->vendedor)->create();
    Venda::factory()->for($outroVendedor)->create();

    actingAs($this->vendedorUser);

    $resource = VendaResource::getEloquentQuery();
    expect($resource->count())->toBe(1)
        ->and($resource->first()->vendedor_id)->toBe($this->vendedor->getKey());
});

it('vendedor não acessa despesas', function () {
    actingAs($this->vendedorUser);

    expect(Gate::allows('viewAny', Despesa::class))->toBeFalse()
        ->and(Gate::allows('viewAny', Fornecedor::class))->toBeFalse()
        ->and(Gate::allows('viewAny', Grupo::class))->toBeFalse()
        ->and(Gate::allows('viewAny', Movimentacao::class))->toBeFalse();
});

it('vendedor consulta clientes e produtos', function () {
    actingAs($this->vendedorUser);

    expect(Gate::allows('viewAny', Cliente::class))->toBeTrue()
        ->and(Gate::allows('viewAny', Produto::class))->toBeTrue()
        ->and(Gate::allows('create', Cliente::class))->toBeFalse();
});

it('visitante não acessa o painel', function () {
    get(VendaResource::getUrl('index'))
        ->assertRedirect();
});

it('vendedor autenticado acessa listagem de vendas', function () {
    actingAs($this->vendedorUser)
        ->get(VendaResource::getUrl('index'))
        ->assertSuccessful();
});

it('vendedor não acessa listagem de despesas', function () {
    actingAs($this->vendedorUser)
        ->get(DespesaResource::getUrl('index'))
        ->assertForbidden();
});

it('venda registrada por vendedor é atribuída a ele e entra aberta', function () {
    $produto = Produto::factory()->create(['ativo' => true, 'estoque_qtd' => 10]);
    $cidade = Cidade::factory()->create(['ativo' => true]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $cidade->getKey(),
        'quantidade' => 10,
    ]);

    actingAs($this->vendedorUser);

    Livewire::test(CreateVenda::class)
        ->fillForm([
            'cliente_id' => Cliente::factory()->create()->getKey(),
            'itens' => [
                ['produto_id' => $produto->getKey(), 'cidade_id' => $cidade->getKey(), 'quantidade' => 2],
            ],
            'status' => StatusVenda::Fechada->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $venda = Venda::query()->sole();

    expect($venda->vendedor_id)->toBe($this->vendedor->getKey())
        ->and($venda->status)->toBe(StatusVenda::Aberta);
});
