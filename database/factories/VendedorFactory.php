<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendedor>
 */
class VendedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'telefone' => $this->faker->phoneNumber(),
            'comissao_pct' => 5,
            'ativo' => true,
        ];
    }
}
