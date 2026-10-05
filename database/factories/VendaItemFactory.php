<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendaItem>
 */
class VendaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantidade = $this->faker->numberBetween(1, 5);
        $precoUnit = $this->faker->numberBetween(500, 10000);

        return [
            'venda_id' => Venda::factory(),
            'produto_id' => Produto::factory(),
            'quantidade' => $quantidade,
            'preco_unit_int' => $precoUnit,
            'subtotal_int' => $quantidade * $precoUnit,
        ];
    }
}
