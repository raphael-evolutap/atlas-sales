<?php

use App\Filament\Resources\Fornecedors\Pages\CreateFornecedor;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    /** @var TestCase $this */
    $this->seed(ShieldSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    actingAs($admin);
});

it('busca dados do cnpj na brasilapi e preenche o formulário', function () {
    Http::fake([
        'brasilapi.com.br/api/cnpj/v1/*' => Http::response([
            'razao_social' => 'Empresa Teste LTDA',
            'logradouro' => 'Avenida Paulista',
            'numero' => '1000',
            'municipio' => 'São Paulo',
            'uf' => 'SP',
            'cep' => '01310100',
            'ddd_telefone_1' => '11987654321',
        ]),
    ]);

    Livewire::test(CreateFornecedor::class)
        ->fillForm(['cnpj' => '12.345.678/0001-95'])
        ->callAction(TestAction::make('buscarCnpj')->schemaComponent('cnpj'))
        ->assertFormSet([
            'nome' => 'Empresa Teste LTDA',
            'endereco' => 'Avenida Paulista, 1000',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'cep' => '01310100',
            'telefone' => '11987654321',
        ])
        ->assertNotified('Dados do CNPJ carregados');
});

it('notifica erro quando cnpj não tem 14 dígitos', function () {
    Http::fake();

    Livewire::test(CreateFornecedor::class)
        ->fillForm(['cnpj' => '123'])
        ->callAction(TestAction::make('buscarCnpj')->schemaComponent('cnpj'))
        ->assertNotified('CNPJ inválido');

    Http::assertNothingSent();
});

it('notifica erro quando brasilapi não encontra o cnpj', function () {
    Http::fake([
        'brasilapi.com.br/api/cnpj/v1/*' => Http::response(['message' => 'Not found'], 404),
    ]);

    Livewire::test(CreateFornecedor::class)
        ->fillForm(['cnpj' => '12.345.678/0001-95'])
        ->callAction(TestAction::make('buscarCnpj')->schemaComponent('cnpj'))
        ->assertNotified('CNPJ não encontrado');
});
