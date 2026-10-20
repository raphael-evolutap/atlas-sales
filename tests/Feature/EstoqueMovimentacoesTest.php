<?php

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Exceptions\EstoqueInsuficienteException;
use App\Filament\Resources\Movimentacaos\Pages\ListMovimentacaos;
use App\Filament\Resources\Produtos\Pages\CreateProduto;
use App\Filament\Resources\Produtos\Pages\EditProduto;
use App\Filament\Resources\Produtos\RelationManagers\EstoquesRelationManager;
use App\Models\Cidade;
use App\Models\Movimentacao;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\ProdutoLote;
use App\Models\User;
use App\Services\EstoqueService;
use Database\Seeders\ShieldSeeder;
use Filament\Actions\Testing\TestAction;
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
        lote: lotePadrao($produto),
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
        lote: lotePadrao($produto),
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
        lote: lotePadrao($produto),
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
        lote: lotePadrao($produto),
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
            lote: lotePadrao($produto),
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
        lote: lotePadrao($produto),
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

it('saída de um lote não afeta outro lote da mesma cidade', function () {
    $produto = Produto::factory()->create();
    $loteA = ProdutoLote::factory()->for($produto)->create(['numero' => 'A']);
    $loteB = ProdutoLote::factory()->for($produto)->create(['numero' => 'B']);

    $this->service->registrar($loteA, $this->cidade, TipoMovimentacao::Entrada, 5, MotivoMovimentacao::Compra);
    $this->service->registrar($loteB, $this->cidade, TipoMovimentacao::Entrada, 8, MotivoMovimentacao::Compra);
    $saida = $this->service->registrar($loteB, $this->cidade, TipoMovimentacao::Saida, 3, MotivoMovimentacao::Perda);

    $estoque = $produto->estoques()->sole();

    expect($estoque->lotes()->where('lote_id', $loteA->getKey())->value('quantidade'))->toBe(5)
        ->and($estoque->lotes()->where('lote_id', $loteB->getKey())->value('quantidade'))->toBe(5)
        ->and($estoque->quantidade)->toBe(10)
        ->and($produto->fresh()->estoque_qtd)->toBe(10)
        ->and($saida->lote_id)->toBe($loteB->getKey());
});

it('rejeita saída acima do saldo do lote mesmo com saldo na cidade', function () {
    $produto = Produto::factory()->create();
    $loteA = ProdutoLote::factory()->for($produto)->create();
    $loteB = ProdutoLote::factory()->for($produto)->create();

    $this->service->registrar($loteA, $this->cidade, TipoMovimentacao::Entrada, 2, MotivoMovimentacao::Compra);
    $this->service->registrar($loteB, $this->cidade, TipoMovimentacao::Entrada, 50, MotivoMovimentacao::Compra);

    $this->service->registrar($loteA, $this->cidade, TipoMovimentacao::Saida, 3, MotivoMovimentacao::Perda);
})->throws(EstoqueInsuficienteException::class);

it('cadastro do produto cria o lote do estoque inicial', function () {
    $this->seed(ShieldSeeder::class);
    $this->user->assignRole('Admin');

    Livewire::test(CreateProduto::class)
        ->fillForm([
            'nome' => 'Cimento',
            'sku' => 'CIM-01',
            'unidade' => 'un',
            'preco_custo_int' => '10,00',
            'preco_venda_int' => '15,00',
            'comissao_pct' => 5,
            'estoque_minimo' => 0,
            'estoques' => [
                ['cidade_id' => $this->cidade->getKey(), 'lote' => 'L2024-07', 'quantidade' => 12, 'estoque_minimo' => 2],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $produto = Produto::query()->where('sku', 'CIM-01')->sole();

    expect($produto->lotes()->sole()->numero)->toBe('L2024-07')
        ->and($produto->estoques()->sole()->lotes()->sole()->quantidade)->toBe(12)
        ->and($produto->estoque_qtd)->toBe(12);
});

it('adiciona estoque em um lote existente pelo produto', function () {
    $this->seed(ShieldSeeder::class);
    $this->user->assignRole('Admin');

    $produto = Produto::factory()->create();
    $lote = ProdutoLote::factory()->for($produto)->create();

    Livewire::test(EstoquesRelationManager::class, [
        'ownerRecord' => $produto,
        'pageClass' => EditProduto::class,
    ])
        ->callAction(TestAction::make('adicionarEstoque')->table(), [
            'cidade_id' => $this->cidade->getKey(),
            'lote_numero' => $lote->numero,
            'quantidade' => 7,
            'estoque_minimo' => 1,
        ])
        ->assertHasNoFormErrors();

    expect($produto->estoques()->sole()->lotes()->sole())
        ->lote_id->toBe($lote->getKey())
        ->quantidade->toBe(7);
});

it('adiciona estoque em um lote novo digitado no formulário', function () {
    $this->seed(ShieldSeeder::class);
    $this->user->assignRole('Admin');

    $produto = Produto::factory()->create();

    Livewire::test(EstoquesRelationManager::class, [
        'ownerRecord' => $produto,
        'pageClass' => EditProduto::class,
    ])
        ->callAction(TestAction::make('adicionarEstoque')->table(), [
            'cidade_id' => $this->cidade->getKey(),
            'lote_numero' => 'L2026-10',
            'quantidade' => 4,
            'estoque_minimo' => 0,
        ])
        ->assertHasNoFormErrors();

    expect($produto->lotes()->sole()->numero)->toBe('L2026-10')
        ->and($produto->estoques()->sole()->quantidade)->toBe(4);
});

it('ajuste não aceita remover mais que o saldo do lote na cidade', function () {
    $this->seed(ShieldSeeder::class);
    $this->user->assignRole('Admin');

    $produto = Produto::factory()->create();
    $estoque = ProdutoEstoque::factory()->for($produto)->for($this->cidade)->create(['quantidade' => 3]);

    Livewire::test(EstoquesRelationManager::class, [
        'ownerRecord' => $produto,
        'pageClass' => EditProduto::class,
    ])
        ->callAction(TestAction::make('ajustar')->table($estoque), [
            'lote_id' => lotePadrao($produto)->getKey(),
            'quantidade' => -4,
        ])
        ->assertHasFormErrors(['quantidade' => 'Não é possível remover mais do que o saldo do lote nesta cidade (3).']);

    expect($estoque->refresh()->quantidade)->toBe(3);
});

it('não exclui em massa cidade com saldo', function () {
    $this->seed(ShieldSeeder::class);
    $this->user->assignRole('Admin');

    $produto = Produto::factory()->create(['estoque_qtd' => 5]);
    $comSaldo = ProdutoEstoque::factory()->for($produto)->for($this->cidade)->create(['quantidade' => 5]);
    $zerado = ProdutoEstoque::factory()->for($produto)->for(Cidade::factory())->create(['quantidade' => 0]);

    Livewire::test(EstoquesRelationManager::class, [
        'ownerRecord' => $produto,
        'pageClass' => EditProduto::class,
    ])
        ->selectTableRecords([$comSaldo->getKey(), $zerado->getKey()])
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(ProdutoEstoque::query()->whereKey($comSaldo->getKey())->exists())->toBeTrue()
        ->and(ProdutoEstoque::query()->whereKey($zerado->getKey())->exists())->toBeFalse()
        ->and($comSaldo->lotes()->sole()->quantidade)->toBe(5);
});
