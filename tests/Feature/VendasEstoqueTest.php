<?php

use App\Enums\MotivoMovimentacao;
use App\Enums\StatusVenda;
use App\Exceptions\AcaoInvalidaException;
use App\Exceptions\EstoqueInsuficienteException;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\User;
use App\Models\Vendedor;
use App\Services\VendaService;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());
    $this->service = app(VendaService::class);
    $this->cliente = Cliente::factory()->create();
    $this->vendedor = Vendedor::factory()->create();
    $this->cidade = Cidade::factory()->create();
    $this->outraCidade = Cidade::factory()->create();
});

function dadosVenda(array $overrides = []): array
{
    return [
        'cliente_id' => test()->cliente->getKey(),
        'vendedor_id' => test()->vendedor->getKey(),
        'data_venda' => now()->toDateString(),
        'status' => StatusVenda::Aberta,
        'desconto_int' => 0,
        ...$overrides,
    ];
}

function itemVenda(Produto $produto, Cidade $cidade, int $quantidade): array
{
    return [
        'produto_id' => $produto->getKey(),
        'cidade_id' => $cidade->getKey(),
        'quantidade' => $quantidade,
    ];
}

it('venda aberta não toca estoque', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    $venda = $this->service->criar(dadosVenda(), [itemVenda($produto, $this->cidade, 2)]);

    expect($venda->status)->toBe(StatusVenda::Aberta)
        ->and($produto->fresh()->estoque_qtd)->toBe(10)
        ->and($produto->movimentacoes()->count())->toBe(0);
});

it('fechar venda baixa estoque da cidade do item e cria movimentação', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    $venda = $this->service->criar(dadosVenda(), [itemVenda($produto, $this->cidade, 2)]);
    $this->service->fechar($venda);

    expect($produto->fresh()->estoque_qtd)->toBe(8)
        ->and($produto->estoques()->first()->quantidade)->toBe(8)
        ->and($venda->fresh()->status)->toBe(StatusVenda::Fechada)
        ->and($venda->movimentacoes()->where('tipo', 'saida')->where('motivo', 'venda')->count())->toBe(1);
});

it('baixa estoque somente da cidade escolhida no item', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 20]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->outraCidade->getKey(),
        'quantidade' => 10,
    ]);

    $venda = $this->service->criar(dadosVenda(), [itemVenda($produto, $this->cidade, 3)]);
    $this->service->fechar($venda);

    expect($produto->estoques()->where('cidade_id', $this->cidade->getKey())->first()->quantidade)->toBe(7)
        ->and($produto->estoques()->where('cidade_id', $this->outraCidade->getKey())->first()->quantidade)->toBe(10)
        ->and($produto->fresh()->estoque_qtd)->toBe(17);
});

it('venda criada já fechada baixa estoque', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    $venda = $this->service->criar(dadosVenda(['status' => StatusVenda::Fechada]), [itemVenda($produto, $this->cidade, 2)]);

    expect($produto->fresh()->estoque_qtd)->toBe(8)
        ->and($venda->fresh()->status)->toBe(StatusVenda::Fechada);
});

it('cancelar venda devolve estoque na cidade do item', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    $venda = $this->service->criar(dadosVenda(['status' => StatusVenda::Fechada]), [itemVenda($produto, $this->cidade, 2)]);
    $this->service->cancelar($venda);

    expect($produto->fresh()->estoque_qtd)->toBe(10)
        ->and($produto->estoques()->first()->quantidade)->toBe(10)
        ->and($venda->fresh()->status)->toBe(StatusVenda::Cancelada)
        ->and($venda->movimentacoes()->where('tipo', 'entrada')->where('motivo', MotivoMovimentacao::Estorno)->count())->toBe(1);
});

it('rejeita venda fechada com estoque insuficiente na cidade', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 1]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 1,
    ]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->outraCidade->getKey(),
        'quantidade' => 50,
    ]);

    $this->service->criar(dadosVenda(['status' => StatusVenda::Fechada]), [itemVenda($produto, $this->cidade, 5)]);
})->throws(EstoqueInsuficienteException::class);

it('calcula total com snapshot de preço', function () {
    $produto = Produto::factory()->create(['preco_venda_int' => 1050]);

    $venda = $this->service->criar(dadosVenda(['desconto_int' => 50]), [itemVenda($produto, $this->cidade, 2)]);

    expect($venda->valor_total_int)->toBe(2050)
        ->and($venda->itens->first()->preco_unit_int)->toBe(1050);
});

it('snapshot de preço não muda quando produto muda', function () {
    $produto = Produto::factory()->create(['preco_venda_int' => 1000]);

    $venda = $this->service->criar(dadosVenda(), [itemVenda($produto, $this->cidade, 1)]);

    $produto->update(['preco_venda_int' => 9999]);

    expect($venda->itens->first()->fresh()->preco_unit_int)->toBe(1000);
});

it('não fecha venda cancelada ou já fechada', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    $venda = $this->service->criar(dadosVenda(['status' => StatusVenda::Fechada]), [itemVenda($produto, $this->cidade, 1)]);

    $this->service->fechar($venda);
})->throws(AcaoInvalidaException::class);
