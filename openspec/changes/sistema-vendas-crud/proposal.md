## Why

O projeto precisa de um sistema de vendas para registro e controle interno (sem pagamento online nesta fase). Hoje o projeto Laravel 13 + Filament 5 está limpo — nenhum model, migration ou painel existe. Este change estabelece a base completa do domínio.

## What Changes

- Criar painel Filament com autenticação e roles via filament-shield (spatie/laravel-permission): `Admin` (acesso total) e `Vendedor` (acesso restrito).
- Criar módulos CRUD no Filament:
  - **Grupos** — grupos de clientes.
  - **Clientes** — vinculados a um grupo.
  - **Fornecedores**.
  - **Vendedores** — perfil 1:1 com `users` (vendedor pode ser um usuário com login).
  - **Produtos** — vinculados a fornecedor e grupo (opcional).
  - **Movimentações de estoque** — registro completo de entrada/saída/ajuste por produto.
  - **Vendas** — com itens (`venda_items`); criação/cancelamento atualiza estoque automaticamente via movimentação.
  - **Despesas** — com categorias em tabela própria, seed com opções iniciais.
- Migrations para todas as tabelas (NÃO executar migrations — apenas criar arquivos).
- Models Eloquent com relacionamentos, factories e seeders.
- Valores monetários armazenados como **integer em centavos** (1 = R$ 0,01).
- `estoque_qtd` no produto é cache; fonte de verdade = movimentações.
- `vendedores.comissao_pct` integer, campo oculto no front (forms/tables Filament).

## Capabilities

### New Capabilities
- `catalogo-produtos`: CRUD de produtos, grupos de clientes e fornecedores; valores em centavos.
- `estoque-movimentacoes`: registro de movimentações de estoque (entrada/saída/ajuste), saldo cache no produto, vínculo com vendas.
- `vendas`: registro de vendas com itens, status (aberta/fechada/cancelada), atualização automática de estoque em transação, estorno no cancelamento.
- `cadastros-pessoas`: CRUD de clientes (com grupos) e vendedores (perfil de usuário).
- `despesas`: CRUD de despesas com categorias próprias (seed inicial) e controle de pagamento.
- `acesso-papeis`: painel Filament, roles Admin/Vendedor via Shield, escopo de visão do vendedor (só suas vendas, leitura de clientes/produtos).

### Modified Capabilities

(nenhuma — projeto novo)

## Impact

- **Código novo**: `app/Models/*`, `app/Filament/Resources/*`, `database/migrations/*`, `database/factories/*`, `database/seeders/*`.
- **Dependências**: já instaladas (filament/filament 5.8, bezhansalleh/filament-shield 4.3, spatie/laravel-permission). Sem novas dependências.
- **Banco**: novas tabelas; migrations criadas mas NÃO executadas (decisão do usuário).
- **Sem pagamento online** nesta fase — sistema apenas de registro e controle.
