## ADDED Requirements

### Requirement: Painel Filament com autenticação
O sistema SHALL ter painel Filament com login. Acesso ao painel SHALL exigir autenticação.

#### Scenario: Acesso sem login
- **WHEN** visitante abre o painel sem estar autenticado
- **THEN** redirecionado para login

### Requirement: Roles Admin e Vendedor
O sistema SHALL definir roles via filament-shield: `Admin` (acesso total a todos os recursos) e `Vendedor` (escopo restrito).

#### Scenario: Admin acessa tudo
- **WHEN** Admin loga no painel
- **THEN** vê todos os menus: cadastros, produtos, estoque, vendas, despesas

### Requirement: Escopo do vendedor
Vendedor SHALL ver apenas suas próprias vendas; SHALL ter leitura de clientes e produtos; SHALL NOT acessar despesas, fornecedores, grupos ou movimentações de estoque.

#### Scenario: Vendedor vê só suas vendas
- **WHEN** Vendedor abre listagem de vendas
- **THEN** vê apenas vendas onde vendedor_id = seu perfil

#### Scenario: Vendedor sem despesas
- **WHEN** Vendedor loga no painel
- **THEN** menu de despesas não aparece e acesso direto é negado
