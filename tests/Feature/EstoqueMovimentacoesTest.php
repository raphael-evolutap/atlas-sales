<?php

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Exceptions\EstoqueInsuficienteException;
use App\Filament\Resources\Movimentacaos\Pages\ListMovimentacaos;
use App\Models\Cidade;
use App\Models\Movimentacao;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\User;
use App\Services\EstoqueService;
use Database\Seeders\ShieldSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(EstoqueService::class);
    $this->user = User::factory()->create();
    $this->cidade = Cidade::factory()->create();
    actingAs($this->user);
});

it('entrada incrementa cache, saldo da cidade e registra movimentação', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 0]);

    $this->service->registrar(
        produto: $produto,
        cidade: $this->cidade,
        tipo: TipoMovimentacao::Entrada,
        quantidade: 50,
        motivo: MotivoMovimentacao::Compra,
    );

    expect($produto->fresh()->estoque_qtd)->toBe(50)
        ->and($produto->estoques()->where('cidade_id', $this->cidade->getKey())->first()->quantidade)->toBe(50)
        ->and($produto->movimentacoes()->count())->toBe(1)
        ->and($produto->movimentacoes()->first()->user_id)->toBe($this->user->id)
        ->and($produto->movimentacoes()->first()->cidade_id)->toBe($this->cidade->getKey());
});

it('saida decrementa saldo da cidade e o cache', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    $this->service->registrar(
        produto: $produto,
        cidade: $this->cidade,
        tipo: TipoMovimentacao::Saida,
        quantidade: 3,
        motivo: MotivoMovimentacao::Perda,
    );

    expect($produto->fresh()->estoque_qtd)->toBe(7)
        ->and($produto->estoques()->first()->quantidade)->toBe(7);
});

it('ajuste negativo decrementa saldo da cidade e o cache', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 10,
    ]);

    $this->service->registrar(
        produto: $produto,
        cidade: $this->cidade,
        tipo: TipoMovimentacao::Ajuste,
        quantidade: -3,
        motivo: MotivoMovimentacao::Ajuste,
    );

    expect($produto->fresh()->estoque_qtd)->toBe(7)
        ->and($produto->estoques()->first()->quantidade)->toBe(7);
});

it('rejeita saída que deixa saldo da cidade negativo', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 2]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 2,
    ]);

    $this->service->registrar(
        produto: $produto,
        cidade: $this->cidade,
        tipo: TipoMovimentacao::Saida,
        quantidade: 5,
        motivo: MotivoMovimentacao::Venda,
    );
})->throws(EstoqueInsuficienteException::class);

it('não grava nada quando saída é rejeitada', function () {
    $produto = Produto::factory()->create(['estoque_qtd' => 2]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 2,
    ]);

    try {
        $this->service->registrar(
            produto: $produto,
            cidade: $this->cidade,
            tipo: TipoMovimentacao::Saida,
            quantidade: 5,
            motivo: MotivoMovimentacao::Venda,
        );
    } catch (EstoqueInsuficienteException) {
    }

    expect($produto->fresh()->estoque_qtd)->toBe(2)
        ->and($produto->estoques()->first()->quantidade)->toBe(2)
        ->and($produto->movimentacoes()->count())->toBe(0);
});

it('saída em uma cidade não afeta saldo de outra', function () {
    $outra = Cidade::factory()->create(['nome' => 'Mogi das Cruzes']);
    $produto = Produto::factory()->create(['estoque_qtd' => 10]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $this->cidade->getKey(),
        'quantidade' => 5,
    ]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $outra->getKey(),
        'quantidade' => 5,
    ]);

    $this->service->registrar(
        produto: $produto,
        cidade: $this->cidade,
        tipo: TipoMovimentacao::Saida,
        quantidade: 5,
        motivo: MotivoMovimentacao::Venda,
    );

    expect($produto->fresh()->estoque_qtd)->toBe(5)
        ->and($produto->estoques()->where('cidade_id', $outra->getKey())->first()->quantidade)->toBe(5);
});

it('listagem mostra as movimentações mais recentes primeiro', function () {
    $this->seed(ShieldSeeder::class);
    $this->user->assignRole('Admin');

    $antigas = Movimentacao::factory()->count(10)->create(['created_at' => now()->subDay()]);
    $recente = Movimentacao::factory()->create();

    Livewire::test(ListMovimentacaos::class)
        ->assertCanSeeTableRecords([$recente])
        ->assertCanNotSeeTableRecords([$antigas->first()]);
});
