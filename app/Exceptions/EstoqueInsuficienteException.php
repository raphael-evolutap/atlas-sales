<?php

namespace App\Exceptions;

use RuntimeException;

class EstoqueInsuficienteException extends RuntimeException
{
    public function __construct(public readonly string $produtoNome, public readonly int $saldo, public readonly int $solicitado)
    {
        parent::__construct("Estoque insuficiente para {$produtoNome}: saldo {$saldo}, solicitado {$solicitado}.");
    }
}
