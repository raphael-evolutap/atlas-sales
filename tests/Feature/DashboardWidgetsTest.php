<?php

use App\Models\User;
use App\Models\Vendedor;
use Database\Seeders\ShieldSeeder;

use function Pest\Laravel\actingAs;

it('dashboard carrega para admin', function () {
    $this->seed(ShieldSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('dashboard carrega para vendedor (sem widgets de despesas)', function () {
    $this->seed(ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Vendedor');
    Vendedor::factory()->for($user)->create();

    actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});
