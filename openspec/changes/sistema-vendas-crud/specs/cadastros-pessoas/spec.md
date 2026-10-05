## ADDED Requirements

### Requirement: CRUD de clientes
O sistema SHALL permitir CRUD de clientes com grupo (obrigatório), nome, email, telefone, cpf_cnpj, endereço, cidade, UF, CEP, observações e flag ativo.

#### Scenario: Criar cliente
- **WHEN** Admin salva cliente com grupo selecionado
- **THEN** cliente criado e vinculado ao grupo

### Requirement: CRUD de grupos de clientes
O sistema SHALL permitir CRUD de grupos de clientes (nome, descrição, ativo) usados para segmentar clientes.

#### Scenario: Listar clientes por grupo
- **WHEN** Admin abre listagem de clientes
- **THEN** filtro por grupo disponível

### Requirement: Vendedor como usuário
O sistema SHALL manter tabela `vendedores` com `user_id` único (1:1 com users), telefone e `comissao_pct` integer. `comissao_pct` SHALL ser oculto nos forms e tables do Filament.

#### Scenario: Criar vendedor a partir de usuário
- **WHEN** Admin cria vendedor vinculando um usuário
- **THEN** vendedor criado; usuário pode logar e ver o painel como Vendedor

#### Scenario: Comissão oculta
- **WHEN** qualquer usuário abre form ou tabela de vendedores
- **THEN** campo `comissao_pct` não aparece
