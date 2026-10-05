<?php

namespace App\Support;

class Money
{
    /**
     * Converte centavos (int) para string editável "1.234,56".
     */
    public static function toView(?int $centavos): ?string
    {
        if ($centavos === null) {
            return null;
        }

        return number_format($centavos / 100, 2, ',', '.');
    }

    /**
     * Converte string "1.234,56" para centavos (int).
     */
    public static function fromView(?string $valor): int
    {
        $normalizado = str_replace('.', '', (string) $valor);
        $normalizado = str_replace(',', '.', $normalizado);

        return (int) round(((float) $normalizado) * 100);
    }
}
