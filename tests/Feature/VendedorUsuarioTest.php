<?php

use App\Filament\Resources\Vendedors\Pages\CreateVendedor;
use App\Filament\Resources\Vendedors\Pages\EditVendedor;
use App\Models\User;
use App\Models\Vendedor;
use Database\Seeders\ShieldSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    /** @var TestCase $this */
    $this->seed(ShieldSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    actingAs($admin);
});

it('cria usuário e associa ao vendedor ao salvar', function () {
    Livewire::test(CreateVendedor::class)
        ->fillForm([
            'user_name' => 'João Vendedor',
            'user_email' => 'joao@example.com',
            'user_password' => 'senha-secreta',
            'user_password_confirmation' => 'senha-secreta',
            'telefone' => '11999999999',
            'ativo' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'joao@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('João Vendedor')
        ->and($user->hasRole('Vendedor'))->toBeTrue();

    $vendedor = Vendedor::query()->where('user_id', $user->getKey())->first();

    expect($vendedor)->not->toBeNull()
        ->and($vendedor->telefone)->toBe('11999999999')
        ->and($vendedor->ativo)->toBeTrue();
});

it('valida email duplicado de usuário', function () {
    User::factory()->create(['email' => 'joao@example.com']);

    Livewire::test(CreateVendedor::class)
        ->fillForm([
            'user_name' => 'João Vendedor',
            'user_email' => 'joao@example.com',
            'user_password' => 'senha-secreta',
            'user_password_confirmation' => 'senha-secreta',
        ])
        ->call('create')
        ->assertHasFormErrors(['user_email']);

    expect(Vendedor::count())->toBe(0);
});

it('edita dados do usuário sem alterar senha', function () {
    $vendedor = Vendedor::factory()->create();
    $senhaOriginal = $vendedor->user->password;

    Livewire::test(EditVendedor::class, ['record' => $vendedor->getKey()])
        ->fillForm([
            'user_name' => 'Nome Alterado',
            'user_email' => $vendedor->user->email,
            'telefone' => '11888888888',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user = $vendedor->user->fresh();

    expect($user->name)->toBe('Nome Alterado')
        ->and($user->password)->toBe($senhaOriginal)
        ->and($vendedor->fresh()->telefone)->toBe('11888888888');
});

it('altera senha ao informar nova senha na edição', function () {
    $vendedor = Vendedor::factory()->create();
    $senhaOriginal = $vendedor->user->password;

    Livewire::test(EditVendedor::class, ['record' => $vendedor->getKey()])
        ->fillForm([
            'user_name' => $vendedor->user->name,
            'user_email' => $vendedor->user->email,
            'user_password' => 'nova-senha-123',
            'user_password_confirmation' => 'nova-senha-123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user = $vendedor->user->fresh();

    expect($user->password)->not->toBe($senhaOriginal);
});
