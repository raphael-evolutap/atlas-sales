# Tasks

## 1. Banco de dados (migrations — NÃO executar)

- [x] 1.1 Migration `grupos` (nome, descricao, ativo, softdeletes)
- [x] 1.2 Migration `clientes` (grupo_id FK, nome, email, telefone, cpf_cnpj, endereco, cidade, uf, cep, observacoes, ativo, softdeletes)
- [x] 1.3 Migration `fornecedores` (nome, cnpj, contato_nome, email, telefone, endereco, cidade, uf, cep, observacoes, ativo, softdeletes)
- [x] 1.4 Migration `vendedores` (user_id FK unique, telefone, comissao_pct int, ativo)
- [x] 1.5 Migration `produtos` (fornecedor_id FK nullable, grupo_id FK nullable, nome, sku unique, unidade, preco_custo_int, preco_venda_int, estoque_qtd default 0, estoque_minimo, ativo, softdeletes)
- [x] 1.6 Migration `movimentacoes` (produto_id FK, tipo enum entrada/saida/ajuste, quantidade, motivo enum compra/venda/estorno/ajuste/perda, venda_id FK nullable, observacoes, user_id FK)
- [x] 1.7 Migration `vendas` (cliente_id FK, vendedor_id FK, data_venda, status enum aberta/fechada/cancelada, valor_total_int, desconto_int, observacoes, softdeletes)
- [x] 1.8 Migration `venda_items` (venda_id FK, produto_id FK, quantidade, preco_unit_int, subtotal_int)
- [x] 1.9 Migration `despesa_categorias` (nome, ativo)
- [x] 1.10 Migration `despesas` (despesa_categoria_id FK, descricao, valor_int, data, fornecedor_id FK nullable, pago bool, data_pagamento nullable, observacoes, softdeletes)

## 2. Models e relacionamentos

- [x] 2.1 Models: Grupo, Cliente, Fornecedor, Vendedor, Produto, Movimentacao, Venda, VendaItem, DespesaCategoria, Despesa (com casts integer para valores)
- [x] 2.2 Relacionamentos: cliente→grupo, produto→fornecedor/grupo, venda→cliente/vendedor/itens, movimentacao→produto/venda/user, despesa→categoria/fornecedor
- [x] 2.3 Factories para todos os models (com estados úteis: produto com estoque, venda fechada, etc.)

## 3. Regras de negócio (serviços)

- [x] 3.1 Serviço de movimentação de estoque: cria movimentação + atualiza cache `estoque_qtd` em transação, com `lockForUpdate`
- [x] 3.2 Serviço de venda: ao fechar venda → valida saldo, baixa estoque, cria movimentação saida/venda; ao cancelar → estorno
- [x] 3.3 Validação: saída nunca deixa saldo negativo; venda aberta não toca estoque

## 4. Painel Filament

- [x] 4.1 Criar painel admin + navegação agrupada (Cadastros / Operação / Financeiro)
- [x] 4.2 Resources CRUD: Grupos, Clientes, Fornecedores, Produtos, Vendedores (comissao_pct oculto), Movimentações (somente leitura/criação, sem edição), Vendas (com repeater de itens, cálculo de total), Despesas, Despesa Categorias
- [x] 4.3 Exibição de valores em centavos → formato R$ (helper/attribute)
- [x] 4.4 Ação "Fechar venda" e "Cancelar venda" com confirmação

## 5. Acesso e roles (Shield)

- [x] 5.1 Instalar/gerar roles Admin e Vendedor via filament-shield
- [x] 5.2 Policies: vendedor vê só suas vendas (modifyQueryUsing); leitura de clientes/produtos; bloqueio de despesas/fornecedores/grupos/movimentações
- [x] 5.3 Seeder de usuário Admin inicial

## 6. Seeds

- [x] 6.1 Seeder `despesa_categorias` (aluguel, energia, água, internet, salários, marketing, impostos, outro)
- [x] 6.2 Seeder de dados de exemplo (grupos, clientes, fornecedores, produtos) — opcional para dev

## 7. Testes (Pest)

- [x] 7.1 Testes de movimentação: entrada/saída/ajuste atualizam cache; saída sem saldo rejeitada
- [x] 7.2 Testes de venda: fechada baixa estoque + movimentação; cancelada estorna; aberta não toca estoque; estoque insuficiente rejeitado
- [x] 7.3 Testes de acesso: vendedor vê só suas vendas; vendedor sem despesas; Admin vê tudo
- [x] 7.4 Testes CRUD básicos dos resources

## 8. Verificação final

- [x] 8.1 `vendor/bin/pint --dirty`
- [x] 8.2 `php artisan test --compact` (suite completa)
- [x] 8.3 Confirmar com usuário antes de rodar `php artisan migrate` (não executar automaticamente)
