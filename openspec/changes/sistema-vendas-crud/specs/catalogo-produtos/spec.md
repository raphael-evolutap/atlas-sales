## ADDED Requirements

### Requirement: CRUD de produtos
O sistema SHALL permitir CRUD de produtos com nome, SKU único, unidade, `preco_custo_int`, `preco_venda_int`, `estoque_qtd` (cache), `estoque_minimo`, fornecedor e grupo opcionais, flag `ativo`.

#### Scenario: Criar produto
- **WHEN** Admin preenche nome, SKU, preços em centavos e salva
- **THEN** produto criado com `estoque_qtd = 0` e aparece na listagem

#### Scenario: SKU duplicado
- **WHEN** Admin salva produto com SKU já existente
- **THEN** sistema rejeita com erro de validação

### Requirement: CRUD de fornecedores
O sistema SHALL permitir CRUD de fornecedores com nome, CNPJ, contato, email, telefone, endereço, observações e flag `ativo`.

#### Scenario: Criar fornecedor
- **WHEN** Admin salva fornecedor com dados válidos
- **THEN** fornecedor criado e disponível para vínculo em produtos e despesas

### Requirement: CRUD de grupos de clientes
O sistema SHALL permitir CRUD de grupos de clientes (nome, descrição, ativo).

#### Scenario: Criar grupo
- **WHEN** Admin salva grupo com nome
- **THEN** grupo criado e disponível para vínculo em clientes

### Requirement: Valores monetários em centavos
Todos os valores monetários SHALL ser armazenados como integer em centavos (1 = R$ 0,01).

#### Scenario: Preço em centavos
- **WHEN** produto salvo com `preco_venda_int = 1050`
- **THEN** valor armazenado é exatamente 1050 e exibido como R$ 10,50
