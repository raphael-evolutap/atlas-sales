<?php

namespace Database\Seeders;

use App\Enums\MotivoMovimentacao;
use App\Enums\StatusVenda;
use App\Enums\TipoMovimentacao;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Despesa;
use App\Models\DespesaCategoria;
use App\Models\Fornecedor;
use App\Models\Grupo;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\EstoqueService;
use App\Services\VendaService;
use Illuminate\Database\Seeder;

class DadosExemploSeeder extends Seeder
{
    /**
     * @var array<int, array{nome: string, sku: string, custo: int, venda: int, minimo: int}>
     */
    private array $produtos = [
        ['nome' => 'Cimento CP-II 50kg', 'sku' => 'CIM-050', 'custo' => 2890, 'venda' => 3590, 'minimo' => 20],
        ['nome' => 'Areia Média Sacada 20kg', 'sku' => 'ARE-020', 'custo' => 850, 'venda' => 1200, 'minimo' => 30],
        ['nome' => 'Tijolo Cerâmico 8 Furos', 'sku' => 'TIJ-008', 'custo' => 190, 'venda' => 290, 'minimo' => 500],
        ['nome' => 'Tinta Acrílica Branca 18L', 'sku' => 'TIN-018', 'custo' => 18900, 'venda' => 24900, 'minimo' => 10],
        ['nome' => 'Argamassa AC-II 20kg', 'sku' => 'ARG-020', 'custo' => 1450, 'venda' => 1990, 'minimo' => 25],
        ['nome' => 'Fio Flexível 2,5mm 100m', 'sku' => 'FIO-025', 'custo' => 12900, 'venda' => 16900, 'minimo' => 10],
        ['nome' => 'Tubo PVC 25mm 6m', 'sku' => 'TUB-025', 'custo' => 2100, 'venda' => 2990, 'minimo' => 15],
        ['nome' => 'Parafuso Sextavado 10un', 'sku' => 'PAR-010', 'custo' => 690, 'venda' => 990, 'minimo' => 40],
    ];

    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@atlas.test'],
            ['name' => 'Admin', 'password' => 'password'],
        );
        $admin->assignRole('Admin');

        $grupos = $this->seedGrupos();
        $fornecedores = $this->seedFornecedores();
        $vendedor = $this->seedVendedor();
        $clientes = $this->seedClientes($grupos);
        $cidades = $this->seedCidades();
        $produtos = $this->seedProdutos($fornecedores, $cidades, $admin);
        $this->seedVendas($clientes, $vendedor, $produtos, $cidades, $admin);
        $this->seedDespesas($fornecedores);
    }

    /**
     * @return array<string, Cidade>
     */
    private function seedCidades(): array
    {
        $dados = [
            ['nome' => 'São Paulo', 'uf' => 'SP'],
            ['nome' => 'Mogi das Cruzes', 'uf' => 'SP'],
            ['nome' => 'Campinas', 'uf' => 'SP'],
        ];

        $cidades = [];

        foreach ($dados as $item) {
            $cidades[$item['nome']] = Cidade::firstOrCreate(
                ['nome' => $item['nome']],
                [...$item, 'ativo' => true],
            );
        }

        return $cidades;
    }

    /**
     * @return array<string, Grupo>
     */
    private function seedGrupos(): array
    {
        $grupos = [];

        foreach (['1', '2', '3', '4'] as $nome) {
            $grupos[$nome] = Grupo::firstOrCreate(['nome' => $nome], ['ativo' => true]);
        }

        return $grupos;
    }

    /**
     * @return array<string, Fornecedor>
     */
    private function seedFornecedores(): array
    {
        $dados = [
            ['nome' => 'Casa do Construtor Distribuidora', 'cnpj' => '12.345.678/0001-90', 'cidade' => 'São Paulo', 'uf' => 'SP'],
            ['nome' => 'Tintas & Cia Ltda', 'cnpj' => '98.765.432/0001-10', 'cidade' => 'Campinas', 'uf' => 'SP'],
            ['nome' => 'Elétrica Central Atacado', 'cnpj' => '45.678.912/0001-33', 'cidade' => 'Sorocaba', 'uf' => 'SP'],
        ];

        $fornecedores = [];

        foreach ($dados as $item) {
            $fornecedores[$item['nome']] = Fornecedor::firstOrCreate(
                ['nome' => $item['nome']],
                [...$item, 'ativo' => true],
            );
        }

        return $fornecedores;
    }

    private function seedVendedor(): Vendedor
    {
        $user = User::firstOrCreate(
            ['email' => 'vendedor@atlas.test'],
            ['name' => 'Carlos Vendedor', 'password' => 'password'],
        );
        $user->assignRole('Vendedor');

        return Vendedor::firstOrCreate(
            ['user_id' => $user->getKey()],
            ['telefone' => '(11) 98888-7777', 'ativo' => true],
        );
    }

    /**
     * @param  array<string, Grupo>  $grupos
     * @return array<string, Cliente>
     */
    private function seedClientes(array $grupos): array
    {
        $dados = [
            ['nome' => 'Construtora Horizonte Ltda', 'grupo' => '1', 'cpf_cnpj' => '11.222.333/0001-44'],
            ['nome' => 'João Batista Ferreira', 'grupo' => '2', 'cpf_cnpj' => '123.456.789-00'],
            ['nome' => 'Maria Aparecida Souza', 'grupo' => '3', 'cpf_cnpj' => '987.654.321-00'],
            ['nome' => 'Depósito Bom Preço ME', 'grupo' => '4', 'cpf_cnpj' => '22.333.444/0001-55'],
            ['nome' => 'Fernando Costa Arquitetura', 'grupo' => '1', 'cpf_cnpj' => '333.444.555-66'],
            ['nome' => 'Hidráulica São Jorge', 'grupo' => '2', 'cpf_cnpj' => '44.555.666/0001-77'],
        ];

        $clientes = [];

        foreach ($dados as $item) {
            $clientes[$item['nome']] = Cliente::firstOrCreate(
                ['nome' => $item['nome']],
                [
                    'grupo_id' => $grupos[$item['grupo']]->getKey(),
                    'cpf_cnpj' => $item['cpf_cnpj'],
                    'email' => strtolower(str_replace(' ', '.', $item['nome'])).'@exemplo.com',
                    'telefone' => '(11) 3344-55'.random_int(10, 99),
                    'cidade' => 'São Paulo',
                    'uf' => 'SP',
                    'ativo' => true,
                ],
            );
        }

        return $clientes;
    }

    /**
     * @param  array<string, Fornecedor>  $fornecedores
     * @param  array<string, Cidade>  $cidades
     * @return array<string, Produto>
     */
    private function seedProdutos(array $fornecedores, array $cidades, User $admin): array
    {
        $estoque = app(EstoqueService::class);
        $produtos = [];

        // Distribuição do estoque inicial: 60% São Paulo, 40% Mogi das Cruzes
        $distribuicao = [
            'São Paulo' => 0.6,
            'Mogi das Cruzes' => 0.4,
        ];

        foreach ($this->produtos as $i => $item) {
            $produto = Produto::firstOrCreate(
                ['sku' => $item['sku']],
                [
                    'nome' => $item['nome'],
                    'fornecedor_id' => $fornecedores[array_keys($fornecedores)[$i % count($fornecedores)]]->getKey(),
                    'unidade' => 'un',
                    'preco_custo_int' => $item['custo'],
                    'preco_venda_int' => $item['venda'],
                    'comissao_pct' => 5,
                    'estoque_minimo' => $item['minimo'],
                    'ativo' => true,
                ],
            );

            if ($produto->estoque_qtd === 0) {
                $total = $item['minimo'] * 4;

                foreach ($distribuicao as $cidadeNome => $parcela) {
                    $estoque->registrar(
                        produto: $produto,
                        cidade: $cidades[$cidadeNome],
                        tipo: TipoMovimentacao::Entrada,
                        quantidade: (int) round($total * $parcela),
                        motivo: MotivoMovimentacao::Compra,
                        observacoes: 'Estoque inicial (seed)',
                        user: $admin,
                    );
                }
            }

            $produtos[$item['sku']] = $produto;
        }

        return $produtos;
    }

    /**
     * @param  array<string, Cliente>  $clientes
     * @param  array<string, Produto>  $produtos
     * @param  array<string, Cidade>  $cidades
     */
    private function seedVendas(array $clientes, Vendedor $vendedor, array $produtos, array $cidades, User $admin): void
    {
        $service = app(VendaService::class);
        $saoPaulo = $cidades['São Paulo']->getKey();
        $mogi = $cidades['Mogi das Cruzes']->getKey();

        $jaExiste = fn (Cliente $cliente) => Venda::query()
            ->where('cliente_id', $cliente->getKey())
            ->where('vendedor_id', $vendedor->getKey())
            ->exists();

        if (! $jaExiste($clientes['Construtora Horizonte Ltda'])) {
            $venda1 = $service->criar($this->dadosVenda($clientes['Construtora Horizonte Ltda'], $vendedor), [
                ['produto_id' => $produtos['CIM-050']->getKey(), 'cidade_id' => $saoPaulo, 'quantidade' => 30],
                ['produto_id' => $produtos['ARG-020']->getKey(), 'cidade_id' => $mogi, 'quantidade' => 20],
            ], $admin);
            $service->fechar($venda1);
        }

        if (! $jaExiste($clientes['João Batista Ferreira'])) {
            $venda2 = $service->criar($this->dadosVenda($clientes['João Batista Ferreira'], $vendedor), [
                ['produto_id' => $produtos['TIN-018']->getKey(), 'cidade_id' => $saoPaulo, 'quantidade' => 2],
                ['produto_id' => $produtos['FIO-025']->getKey(), 'cidade_id' => $saoPaulo, 'quantidade' => 1],
            ], $admin);
            $service->fechar($venda2);
        }

        if (! $jaExiste($clientes['Depósito Bom Preço ME'])) {
            $venda3 = $service->criar($this->dadosVenda($clientes['Depósito Bom Preço ME'], $vendedor, StatusVenda::Fechada), [
                ['produto_id' => $produtos['TIJ-008']->getKey(), 'cidade_id' => $mogi, 'quantidade' => 500],
                ['produto_id' => $produtos['ARE-020']->getKey(), 'cidade_id' => $mogi, 'quantidade' => 40],
            ], $admin);
            $service->cancelar($venda3, $admin);
        }

        if (! $jaExiste($clientes['Fernando Costa Arquitetura'])) {
            $service->criar($this->dadosVenda($clientes['Fernando Costa Arquitetura'], $vendedor, StatusVenda::Aberta), [
                ['produto_id' => $produtos['TUB-025']->getKey(), 'cidade_id' => $saoPaulo, 'quantidade' => 10],
            ], $admin);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosVenda(Cliente $cliente, Vendedor $vendedor, StatusVenda $status = StatusVenda::Aberta): array
    {
        return [
            'cliente_id' => $cliente->getKey(),
            'vendedor_id' => $vendedor->getKey(),
            'data_venda' => now()->subDays(random_int(1, 10))->toDateString(),
            'status' => $status,
            'desconto_int' => 0,
        ];
    }

    /**
     * @param  array<string, Fornecedor>  $fornecedores
     */
    private function seedDespesas(array $fornecedores): void
    {
        $categorias = DespesaCategoria::query()->pluck('id', 'nome');

        $dados = [
            ['descricao' => 'Aluguel do depósito', 'categoria' => 'Aluguel', 'valor' => 450000, 'pago' => true],
            ['descricao' => 'Conta de energia', 'categoria' => 'Energia', 'valor' => 89750, 'pago' => true],
            ['descricao' => 'Internet fibra 500MB', 'categoria' => 'Internet', 'valor' => 12990, 'pago' => false],
            ['descricao' => 'Salários equipe vendas', 'categoria' => 'Salários', 'valor' => 1250000, 'pago' => false],
            ['descricao' => 'Impostos DAS', 'categoria' => 'Impostos', 'valor' => 34500, 'pago' => false],
            ['descricao' => 'Frete de mercadorias', 'categoria' => 'Outro', 'valor' => 23000, 'pago' => true, 'fornecedor' => 'Casa do Construtor Distribuidora'],
        ];

        foreach ($dados as $item) {
            Despesa::firstOrCreate(
                ['descricao' => $item['descricao']],
                [
                    'despesa_categoria_id' => $categorias[$item['categoria']],
                    'valor_int' => $item['valor'],
                    'data' => now()->subDays(random_int(1, 20))->toDateString(),
                    'fornecedor_id' => isset($item['fornecedor']) ? $fornecedores[$item['fornecedor']]->getKey() : null,
                    'pago' => $item['pago'],
                    'data_pagamento' => $item['pago'] ? now()->subDays(random_int(1, 5))->toDateString() : null,
                ],
            );
        }
    }
}
