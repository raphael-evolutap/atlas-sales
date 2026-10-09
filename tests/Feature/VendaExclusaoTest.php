<?php

use App\Enums\MotivoMovimentacao;
use App\Enums\StatusVenda;
use App\Exceptions\AcaoInvalidaException;
use App\Filament\Resources\Vendas\Pages\ListVendas;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\VendaService;
use Database\Seeders\ShieldSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $this->vendedorUser = User::factory()->create();
    $this->vendedorUser->assignRole('Vendedor');
    $this->vendedor = Vendedor::factory()->for($this->vendedorUser)->create();

    $this->service = app(VendaService::class);
    $this->cidade = Cidade::factory()->create();
    $this->produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $this->produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    actingAs($this->vendedorUser);
});

function criarVenda(StatusVenda $status = StatusVenda::Aberta): Venda
{
    $venda = test()->service->criar([
        'cliente_id' => Cliente::factory()->create()->getKey(),
        'vendedor_id' => test()->vendedor->getKey(),
        'data_venda' => now()->toDateString(),
        'desconto_int' => 0,
    ], [[
        'produto_id' => test()->produto->getKey(),
        'cidade_id' => test()->cidade->getKey(),
        'quantidade' => 3,
    ]]);

    return match ($status) {
        StatusVenda::Aberta => $venda,
        StatusVenda::Fechada => test()->service->fechar($venda),
        StatusVenda::Cancelada => test()->service->cancelar($venda),
    };
}

it('excluir venda aberta devolve o estoque', function () {
    $venda = criarVenda();
    expect($this->produto->fresh()->estoque_qtd)->toBe(7);

    $this->service->excluir($venda);

    expect($venda->fresh()->trashed())->toBeTrue()
        ->and($this->produto->fresh()->estoque_qtd)->toBe(10)
        ->and($this->produto->estoques()->first()->quantidade)->toBe(10)
        ->and($venda->movimentacoes()->where('motivo', MotivoMovimentacao::Estorno)->count())->toBe(1);
});

it('excluir venda cancelada não devolve o estoque de novo', function () {
    $venda = criarVenda(StatusVenda::Cancelada);

    $this->service->excluir($venda);

    expect($this->produto->fresh()->estoque_qtd)->toBe(10);
});

it('não exclui venda fechada', function () {
    $this->service->excluir(criarVenda(StatusVenda::Fechada));
})->throws(AcaoInvalidaException::class);

it('vendedor só vê excluir em venda aberta', function () {
    $aberta = criarVenda();
    $fechada = criarVenda(StatusVenda::Fechada);
    $cancelada = criarVenda(StatusVenda::Cancelada);

    Livewire::test(ListVendas::class)
        ->assertActionVisible(TestAction::make('delete')->table($aberta))
        ->assertActionHidden(TestAction::make('delete')->table($fechada))
        ->assertActionHidden(TestAction::make('delete')->table($cancelada));
});

it('vendedor exclui venda aberta pela listagem e o estoque volta', function () {
    $venda = criarVenda();

    Livewire::test(ListVendas::class)
        ->callAction(TestAction::make('delete')->table($venda));

    expect($venda->fresh()->trashed())->toBeTrue()
        ->and($this->produto->fresh()->estoque_qtd)->toBe(10);
});

it('admin exclui venda cancelada mas não fechada', function () {
    $cancelada = criarVenda(StatusVenda::Cancelada);
    $fechada = criarVenda(StatusVenda::Fechada);

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    actingAs($admin);

    Livewire::test(ListVendas::class)
        ->assertActionVisible(TestAction::make('delete')->table($cancelada))
        ->assertActionHidden(TestAction::make('delete')->table($fechada));
});

it('exclusão em massa devolve estoque e ignora vendas fechadas', function () {
    $aberta = criarVenda();
    $fechada = criarVenda(StatusVenda::Fechada);

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    actingAs($admin);

    Livewire::test(ListVendas::class)
        ->selectTableRecords([$aberta, $fechada])
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect($aberta->fresh()->trashed())->toBeTrue()
        ->and($fechada->fresh()->trashed())->toBeFalse()
        ->and($this->produto->fresh()->estoque_qtd)->toBe(7);
});
