<?php

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Exceptions\AcaoInvalidaException;
use App\Exceptions\EstoqueInsuficienteException;
use App\Filament\Resources\Movimentacaos\Pages\ListMovimentacaos;
use App\Filament\Resources\Produtos\Pages\EditProduto;
use App\Filament\Resources\Produtos\RelationManagers\EstoquesRelationManager;
use App\Models\Cidade;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\User;
use App\Services\EstoqueService;
use Database\Seeders\ShieldSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
    actingAs($this->admin);

    $this->service = app(EstoqueService::class);
    $this->origem = Cidade::factory()->create(['nome' => 'São Paulo', 'ativo' => true]);
    $this->destino = Cidade::factory()->create(['nome' => 'Mogi das Cruzes', 'ativo' => true]);
    $this->produto = Produto::factory()->create(['estoque_qtd' => 10]);
    $this->estoqueOrigem = ProdutoEstoque::factory()->create([
        'produto_id' => $this->produto->getKey(),
        'cidade_id' => $this->origem->getKey(),
        'quantidade' => 10,
    ]);
});

function saldoEm(Cidade $cidade): int
{
    return (int) ProdutoEstoque::query()
        ->where('produto_id', test()->produto->getKey())
        ->where('cidade_id', $cidade->getKey())
        ->value('quantidade');
}

it('transfere saldo entre cidades sem alterar o total do produto', function () {
    $this->service->transferir($this->produto, $this->origem, $this->destino, 4);

    expect(saldoEm($this->origem))->toBe(6)
        ->and(saldoEm($this->destino))->toBe(4)
        ->and($this->produto->fresh()->estoque_qtd)->toBe(10);
});

it('registra saída e entrada com log da transferência', function () {
    $this->service->transferir($this->produto, $this->origem, $this->destino, 4, 'Reposição da loja');

    $movimentacoes = $this->produto->movimentacoes()->orderBy('id')->get();

    expect($movimentacoes)->toHaveCount(2)
        ->and($movimentacoes->pluck('motivo')->unique()->all())->toBe([MotivoMovimentacao::Transferencia])
        ->and($movimentacoes[0]->tipo)->toBe(TipoMovimentacao::Saida)
        ->and($movimentacoes[0]->cidade_id)->toBe($this->origem->getKey())
        ->and($movimentacoes[1]->tipo)->toBe(TipoMovimentacao::Entrada)
        ->and($movimentacoes[1]->cidade_id)->toBe($this->destino->getKey())
        ->and($movimentacoes[0]->observacoes)->toBe('Transferência de São Paulo para Mogi das Cruzes: Reposição da loja')
        ->and($movimentacoes[0]->user_id)->toBe($this->admin->getKey());
});

it('não grava nada quando falta saldo na origem', function () {
    expect(fn () => $this->service->transferir($this->produto, $this->origem, $this->destino, 11))
        ->toThrow(EstoqueInsuficienteException::class);

    expect(saldoEm($this->origem))->toBe(10)
        ->and(saldoEm($this->destino))->toBe(0)
        ->and($this->produto->movimentacoes()->count())->toBe(0);
});

it('não transfere para a mesma cidade', function () {
    $this->service->transferir($this->produto, $this->origem, $this->origem, 1);
})->throws(AcaoInvalidaException::class);

it('transfere pela listagem de movimentações', function () {
    Livewire::test(ListMovimentacaos::class)
        ->callAction('transferirEstoque', [
            'produto_id' => $this->produto->getKey(),
            'cidade_origem_id' => $this->origem->getKey(),
            'cidade_destino_id' => $this->destino->getKey(),
            'quantidade' => 3,
        ])
        ->assertHasNoFormErrors()
        ->assertNotified('Estoque transferido');

    expect(saldoEm($this->origem))->toBe(7)
        ->and(saldoEm($this->destino))->toBe(3);
});

it('valida quantidade acima do saldo da origem no formulário', function () {
    Livewire::test(ListMovimentacaos::class)
        ->callAction('transferirEstoque', [
            'produto_id' => $this->produto->getKey(),
            'cidade_origem_id' => $this->origem->getKey(),
            'cidade_destino_id' => $this->destino->getKey(),
            'quantidade' => 50,
        ])
        ->assertHasFormErrors(['quantidade']);

    expect(saldoEm($this->origem))->toBe(10);
});

it('transfere a partir do estoque da cidade no produto', function () {
    Livewire::test(EstoquesRelationManager::class, [
        'ownerRecord' => $this->produto,
        'pageClass' => EditProduto::class,
    ])
        ->callAction(TestAction::make('transferir')->table($this->estoqueOrigem), [
            'cidade_destino_id' => $this->destino->getKey(),
            'quantidade' => 5,
        ])
        ->assertHasNoFormErrors();

    expect(saldoEm($this->origem))->toBe(5)
        ->and(saldoEm($this->destino))->toBe(5);
});
