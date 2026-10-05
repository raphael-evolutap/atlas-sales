<?php

namespace App\Enums;

enum StatusVenda: string
{
    case Aberta = 'aberta';
    case Fechada = 'fechada';
    case Cancelada = 'cancelada';
}
