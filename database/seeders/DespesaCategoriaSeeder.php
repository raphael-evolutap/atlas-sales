<?php

namespace Database\Seeders;

use App\Models\DespesaCategoria;
use Illuminate\Database\Seeder;

class DespesaCategoriaSeeder extends Seeder
{
    /**
     * Categorias iniciais de despesa.
     *
     * @var array<int, string>
     */
    private array $categorias = [
        'Aluguel',
        'Energia',
        'Água',
        'Internet',
        'Salários',
        'Marketing',
        'Impostos',
        'Outro',
    ];

    public function run(): void
    {
        foreach ($this->categorias as $nome) {
            DespesaCategoria::firstOrCreate(['nome' => $nome]);
        }
    }
}
