<?php

namespace Database\Factories;

use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fornecedor>
 */
class FornecedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->company(),
            'cnpj' => $this->faker->numerify('##.###.###/####-##'),
            'contato_nome' => $this->faker->name(),
            'email' => $this->faker->unique()->companyEmail(),
            'telefone' => $this->faker->phoneNumber(),
            'endereco' => $this->faker->streetAddress(),
            'cidade' => $this->faker->city(),
            'uf' => $this->faker->stateAbbr(),
            'cep' => $this->faker->numerify('#####-###'),
            'observacoes' => null,
            'ativo' => true,
        ];
    }
}
