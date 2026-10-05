<?php

use App\Models\Cliente;
use App\Models\Despesa;
use App\Models\DespesaCategoria;
use App\Models\Grupo;
use App\Models\Produto;
use App\Support\Money;

it('cria grupo', function () {
    $grupo = Grupo::factory()->create(['nome' => 'VIP']);

    expect($grupo->nome)->toBe('VIP')
        ->and($grupo->ativo)->toBeTrue();
});

it('cria cliente com grupo', function () {
    $grupo = Grupo::factory()->create();
    $cliente = Cliente::factory()->for($grupo)->create(['nome' => 'Maria Silva']);

    expect($cliente->grupo_id)->toBe($grupo->getKey())
        ->and($cliente->fresh()->grupo->nome)->toBe($grupo->nome);
});

it('cria produto com preço em centavos', function () {
    $produto = Produto::factory()->create([
        'preco_custo_int' => 1050,
        'preco_venda_int' => 2000,
        'estoque_qtd' => 0,
    ]);

    expect($produto->preco_custo_int)->toBe(1050)
        ->and($produto->preco_venda_int)->toBe(2000);
});

it('cria despesa paga com data de pagamento', function () {
    $despesa = Despesa::factory()->paga()->create();

    expect($despesa->pago)->toBeTrue()
        ->and($despesa->data_pagamento)->not->toBeNull();
});

it('seed cria 8 categorias iniciais', function () {
    $this->seed(DespesaCategoriaSeeder::class);

    expect(DespesaCategoria::count())->toBe(8);
});

it('formata valores em centavos', function () {
    expect(Money::toView(1050))->toBe('10,50')
        ->and(Money::fromView('10,50'))->toBe(1050)
        ->and(Money::fromView('1.234,56'))->toBe(123456);
});
