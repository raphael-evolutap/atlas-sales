<?php

use App\Enums\StatusVenda;
use App\Filament\Resources\Vendas\Pages\ListVendas;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Vendedor;
use Database\Seeders\ShieldSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $this->vendedorUser = User::factory()->create();
    $this->vendedorUser->assignRole('Vendedor');
    $this->vendedorUser->givePermissionTo(
        collect(['Fechar:Venda', 'Cancelar:Venda', 'EnviarComprovantes:Venda'])
            ->map(fn (string $nome) => Permission::findOrCreate($nome)),
    );
    $this->vendedor = Vendedor::factory()->for($this->vendedorUser)->create();

    $this->venda = Venda::factory()->for($this->vendedor)->create(['status' => StatusVenda::Aberta]);
    $this->produto = Produto::factory()->create(['nome' => 'Cimento CP-II 50kg']);
    VendaItem::factory()->for($this->venda)->for($this->produto)->count(2)->create(['comissao_int' => 300]);

    actingAs($this->vendedorUser);
});

it('vendedor vê a comissão da venda na listagem', function () {
    Livewire::test(ListVendas::class)
        ->assertTableColumnStateSet('itens_sum_comissao_int', 600, $this->venda);
});

it('visualização mostra itens e comissão da venda', function () {
    Livewire::test(ListVendas::class)
        ->mountAction(TestAction::make('view')->table($this->venda))
        ->assertMountedActionModalSee('Cimento CP-II 50kg')
        ->assertMountedActionModalSee('6,00');
});

it('fecha a venda pelo botão da visualização', function () {
    Livewire::test(ListVendas::class)
        ->callAction([
            TestAction::make('view')->table($this->venda),
            TestAction::make('fechar'),
        ]);

    expect($this->venda->fresh()->status)->toBe(StatusVenda::Fechada);
});

it('visualização mostra link para abrir o comprovante', function () {
    Storage::fake('local', ['serve' => true]);

    $this->venda
        ->addMedia(UploadedFile::fake()->createWithContent('pix.pdf', "%PDF-1.4\n%%EOF\n"))
        ->toMediaCollection(Venda::COLECAO_COMPROVANTES);

    Livewire::test(ListVendas::class)
        ->mountAction(TestAction::make('view')->table($this->venda))
        ->assertMountedActionModalSee('pix.pdf')
        ->assertMountedActionModalSeeHtml('pix.pdf?');
});
