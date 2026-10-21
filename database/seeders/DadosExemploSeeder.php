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
use App\Models\ProdutoLote;
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
        // Emagrecedores (agonistas de GLP-1/GIP)
        ['nome' => 'Semaglutida 1mg Caneta', 'sku' => 'SEM-010', 'custo' => 45000, 'venda' => 79900, 'minimo' => 5],
        ['nome' => 'Semaglutida 2,4mg Caneta', 'sku' => 'SEM-024', 'custo' => 72000, 'venda' => 119900, 'minimo' => 5],
        ['nome' => 'Liraglutida 6mg/ml Caneta 3ml', 'sku' => 'LIR-006', 'custo' => 38000, 'venda' => 64900, 'minimo' => 5],
        ['nome' => 'Tirzepatida 5mg Caneta', 'sku' => 'TIR-005', 'custo' => 89000, 'venda' => 149900, 'minimo' => 4],
        ['nome' => 'Tirzepatida 10mg Caneta', 'sku' => 'TIR-010', 'custo' => 120000, 'venda' => 199900, 'minimo' => 4],
        // Peptídeos
        ['nome' => 'BPC-157 5mg Frasco', 'sku' => 'BPC-005', 'custo' => 9000, 'venda' => 18900, 'minimo' => 10],
        ['nome' => 'TB-500 5mg Frasco', 'sku' => 'TB5-005', 'custo' => 11000, 'venda' => 21900, 'minimo' => 10],
        ['nome' => 'Ipamorelin 5mg Frasco', 'sku' => 'IPA-005', 'custo' => 8500, 'venda' => 16900, 'minimo' => 10],
        ['nome' => 'CJC-1295 2mg Frasco', 'sku' => 'CJC-002', 'custo' => 9500, 'venda' => 18500, 'minimo' => 10],
        // Acessório
        ['nome' => 'Água Bacteriostática 10ml', 'sku' => 'AGB-010', 'custo' => 1500, 'venda' => 3500, 'minimo' => 20],
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
            ['nome' => 'Farma Lab Distribuidora', 'cnpj' => '12.345.678/0001-90', 'cidade' => 'São Paulo', 'uf' => 'SP'],
            ['nome' => 'BioPeptídeos Importadora Ltda', 'cnpj' => '98.765.432/0001-10', 'cidade' => 'Campinas', 'uf' => 'SP'],
            ['nome' => 'Insumos Médicos Central', 'cnpj' => '45.678.912/0001-33', 'cidade' => 'Sorocaba', 'uf' => 'SP'],
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
            ['nome' => 'Clínica Horizonte Emagrecimento Ltda', 'grupo' => '1', 'cpf_cnpj' => '11.222.333/0001-44'],
            ['nome' => 'João Batista Ferreira', 'grupo' => '2', 'cpf_cnpj' => '123.456.789-00'],
            ['nome' => 'Maria Aparecida Souza', 'grupo' => '3', 'cpf_cnpj' => '987.654.321-00'],
            ['nome' => 'Farmácia de Manipulação Bom Preço ME', 'grupo' => '4', 'cpf_cnpj' => '22.333.444/0001-55'],
            ['nome' => 'Dr. Fernando Costa Nutrologia', 'grupo' => '1', 'cpf_cnpj' => '333.444.555-66'],
            ['nome' => 'Clínica Vida Leve', 'grupo' => '2', 'cpf_cnpj' => '44.555.666/0001-77'],
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
                $lote = ProdutoLote::doProduto($produto, "{$item['sku']}-L01");

                foreach ($distribuicao as $cidadeNome => $parcela) {
                    $estoque->registrar(
                        lote: $lote,
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

        if (! $jaExiste($clientes['Clínica Horizonte Emagrecimento Ltda'])) {
            $venda1 = $service->criar($this->dadosVenda($clientes['Clínica Horizonte Emagrecimento Ltda'], $vendedor), [
                $this->item($produtos['SEM-010'], $saoPaulo, 3),
                $this->item($produtos['LIR-006'], $mogi, 2),
            ], $admin);
            $service->fechar($venda1);
        }

        if (! $jaExiste($clientes['João Batista Ferreira'])) {
            $venda2 = $service->criar($this->dadosVenda($clientes['João Batista Ferreira'], $vendedor), [
                $this->item($produtos['TIR-005'], $saoPaulo, 2),
                $this->item($produtos['AGB-010'], $saoPaulo, 5),
            ], $admin);
            $service->fechar($venda2);
        }

        if (! $jaExiste($clientes['Farmácia de Manipulação Bom Preço ME'])) {
            $venda3 = $service->criar($this->dadosVenda($clientes['Farmácia de Manipulação Bom Preço ME'], $vendedor, StatusVenda::Fechada), [
                $this->item($produtos['BPC-005'], $mogi, 10),
                $this->item($produtos['TB5-005'], $mogi, 5),
            ], $admin);
            $service->cancelar($venda3, $admin);
        }

        if (! $jaExiste($clientes['Dr. Fernando Costa Nutrologia'])) {
            $service->criar($this->dadosVenda($clientes['Dr. Fernando Costa Nutrologia'], $vendedor, StatusVenda::Aberta), [
                $this->item($produtos['IPA-005'], $saoPaulo, 4),
                $this->item($produtos['CJC-002'], $saoPaulo, 4),
            ], $admin);
        }
    }

    /**
     * Item de venda saindo do primeiro lote do produto.
     *
     * @return array{produto_id: int, cidade_id: int, lote_id: int, quantidade: int}
     */
    private function item(Produto $produto, int $cidadeId, int $quantidade): array
    {
        return [
            'produto_id' => $produto->getKey(),
            'cidade_id' => $cidadeId,
            'lote_id' => (int) $produto->lotes()->orderBy('id')->value('id'),
            'quantidade' => $quantidade,
        ];
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
            ['descricao' => 'Frete refrigerado de mercadorias', 'categoria' => 'Outro', 'valor' => 23000, 'pago' => true, 'fornecedor' => 'Farma Lab Distribuidora'],
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
