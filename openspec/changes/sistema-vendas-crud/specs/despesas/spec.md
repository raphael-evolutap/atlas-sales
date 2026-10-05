## ADDED Requirements

### Requirement: CRUD de despesas
O sistema SHALL permitir CRUD de despesas com descrição, categoria, `valor_int`, data, fornecedor opcional, flag pago, data de pagamento e observações.

#### Scenario: Registrar despesa
- **WHEN** Admin salva despesa com categoria e valor em centavos
- **THEN** despesa criada e listada com filtro por categoria e status pago

#### Scenario: Marcar como paga
- **WHEN** Admin marca despesa como paga
- **THEN** `data_pagamento` preenchida

### Requirement: Categorias de despesa em tabela própria
O sistema SHALL manter tabela `despesa_categorias` (nome, ativo) com seed inicial: aluguel, energia, água, internet, salários, marketing, impostos, outro. Novas categorias SHALL poder ser criadas via CRUD.

#### Scenario: Criar nova categoria
- **WHEN** Admin cria categoria "Frete"
- **THEN** categoria disponível no select de despesas

#### Scenario: Seed inicial
- **WHEN** seeders executados
- **THEN** 8 categorias iniciais existem
