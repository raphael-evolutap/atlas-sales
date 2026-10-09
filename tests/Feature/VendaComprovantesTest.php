<?php

use App\Filament\Resources\Vendas\Pages\CreateVenda;
use App\Filament\Resources\Vendas\Pages\ListVendas;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use Database\Seeders\ShieldSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(ShieldSeeder::class);

    $this->vendedorUser = User::factory()->create();
    $this->vendedorUser->assignRole('Vendedor');
    $this->vendedorUser->givePermissionTo(Permission::findOrCreate('EnviarComprovantes:Venda'));
    $this->vendedor = Vendedor::factory()->for($this->vendedorUser)->create();

    actingAs($this->vendedorUser);
});

it('vendedor anexa comprovante ao registrar a venda', function () {
    $produto = Produto::factory()->create(['ativo' => true, 'estoque_qtd' => 10]);
    $cidade = Cidade::factory()->create(['ativo' => true]);
    ProdutoEstoque::factory()->create([
        'produto_id' => $produto->getKey(),
        'cidade_id' => $cidade->getKey(),
        'quantidade' => 10,
    ]);

    Livewire::test(CreateVenda::class)
        ->fillForm([
            'cliente_id' => Cliente::factory()->create()->getKey(),
            'itens' => [
                ['produto_id' => $produto->getKey(), 'cidade_id' => $cidade->getKey(), 'quantidade' => 1],
            ],
            'comprovantes' => [UploadedFile::fake()->createWithContent('comprovante.pdf', "%PDF-1.4\n%%EOF\n")],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $media = Venda::query()->sole()->getMedia(Venda::COLECAO_COMPROVANTES);

    expect($media)->toHaveCount(1)
        ->and($media->first()->disk)->toBe('local');
    Storage::disk('local')->assertExists($media->first()->getPathRelativeToRoot());
});

it('vendedor anexa comprovante a uma venda já registrada', function () {
    $venda = Venda::factory()->for($this->vendedor)->create();

    Livewire::test(ListVendas::class)
        ->callAction(TestAction::make('comprovantes')->table($venda), [
            'comprovantes' => [UploadedFile::fake()->image('pix.jpg')],
        ])
        ->assertHasNoFormErrors();

    expect($venda->getMedia(Venda::COLECAO_COMPROVANTES))->toHaveCount(1);
});

it('rejeita arquivo que não é imagem nem PDF', function () {
    $venda = Venda::factory()->for($this->vendedor)->create();

    Livewire::test(ListVendas::class)
        ->callAction(TestAction::make('comprovantes')->table($venda), [
            'comprovantes' => [UploadedFile::fake()->create('planilha.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')],
        ])
        ->assertHasFormErrors(['comprovantes']);

    expect($venda->getMedia(Venda::COLECAO_COMPROVANTES))->toHaveCount(0);
});

it('vendedor sem permissão não vê a ação de comprovantes', function () {
    $this->vendedorUser->revokePermissionTo('EnviarComprovantes:Venda');
    $venda = Venda::factory()->for($this->vendedor)->create();

    Livewire::test(ListVendas::class)
        ->assertActionHidden(TestAction::make('comprovantes')->table($venda));
});

it('vendedor envia vários comprovantes de uma vez', function () {
    $venda = Venda::factory()->for($this->vendedor)->create();

    Livewire::test(ListVendas::class)
        ->callAction(TestAction::make('comprovantes')->table($venda), [
            'comprovantes' => [
                UploadedFile::fake()->image('pix-1.jpg'),
                UploadedFile::fake()->createWithContent('boleto.pdf', "%PDF-1.4\n%%EOF\n"),
            ],
        ])
        ->assertHasNoFormErrors();

    expect($venda->getMedia(Venda::COLECAO_COMPROVANTES)->pluck('file_name')->sort()->values()->all())
        ->toBe(['boleto.pdf', 'pix-1.jpg']);
});

it('enviar um comprovante depois mantém os que já existiam', function () {
    $venda = Venda::factory()->for($this->vendedor)->create();
    $venda->addMedia(UploadedFile::fake()->image('pix-1.jpg'))->toMediaCollection(Venda::COLECAO_COMPROVANTES);

    $livewire = Livewire::test(ListVendas::class)
        ->mountAction(TestAction::make('comprovantes')->table($venda));

    $existentes = $livewire->get('mountedActions.0.data.comprovantes');

    $livewire
        ->fillForm(['comprovantes' => [...$existentes, UploadedFile::fake()->image('pix-2.jpg')]])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($venda->fresh()->getMedia(Venda::COLECAO_COMPROVANTES)->pluck('file_name')->sort()->values()->all())
        ->toBe(['pix-1.jpg', 'pix-2.jpg']);
});
