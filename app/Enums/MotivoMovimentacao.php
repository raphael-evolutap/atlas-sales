<?php

namespace App\Enums;

enum MotivoMovimentacao: string
{
    case Compra = 'compra';
    case Venda = 'venda';
    case Estorno = 'estorno';
    case Ajuste = 'ajuste';
    case Perda = 'perda';
    case Transferencia = 'transferencia';

    public function getLabel(): string
    {
        return match ($this) {
            self::Transferencia => 'Transferência',
            default => ucfirst($this->value),
        };
    }
}
