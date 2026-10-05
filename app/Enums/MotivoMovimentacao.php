<?php

namespace App\Enums;

enum MotivoMovimentacao: string
{
    case Compra = 'compra';
    case Venda = 'venda';
    case Estorno = 'estorno';
    case Ajuste = 'ajuste';
    case Perda = 'perda';
}
