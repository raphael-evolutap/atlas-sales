## Context

Projeto Laravel 13 + Filament 5.8 + filament-shield 4.3 (spatie/laravel-permission), recém-criado — sem models, migrations ou painel. Sistema de vendas para registro e controle interno, sem pagamento online. Decisões do usuário:

- Valores monetários em **integer centavos** (1 = R$ 0,01).
- Estoque com **movimentações** (histórico completo) + coluna cache `estoque_qtd` no produto.
- Venda registra itens de produto e **atualiza estoque automaticamente**.
- Vendedor é um usuário (login) que acompanha a própria movimentação.
- Categorias de despesa em tabela própria, com seed inicial.
- Migrations criadas mas **não executadas**.

## Goals / Non-Goals

**Goals:**
- Painel Filament com CRUD completo dos 8 módulos.
- Integridade de estoque: venda/cancelamento atualiza movimentações e cache em transação.
- Controle de acesso: Admin (tudo) / Vendedor (escopo próprio).
- Auditoria: quem fez cada movimentação, snapshot de preço nos itens de venda.

**Non-Goals:**
- Pagamento online, emissão de NF-e, recuperação de senha avançada.
- Multi-tenancy.
- Relatórios avançados (dashboards básicos Filament apenas).

## Decisions

1. **Valores em integer centavos** (`*_int` suffix: `preco_custo_int`, `valor_total_int`...). Alternativa decimal(10,2) descartada — decisão do usuário; integer evita erro de ponto flutuante.
2. **Estoque: movimentações = fonte de verdade; `produtos.estoque_qtd` = cache.** Toda escrita passa por movimentação dentro de transação, atualizando o cache. Alternativa "soma a cada query" descartada (custo O(n) por listagem).
3. **Venda → estoque em transação DB**: criação de venda fechada valida saldo, decrementa cache, grava movimentação `saida/motivo=venda` com `venda_id`. Cancelamento reverte (movimentação `entrada/motivo=estorno`). Venda `aberta` não toca estoque.
4. **Snapshot de preço**: `venda_items.preco_unit_int` copiado do produto na venda — mudança futura de preço não altera vendas passadas.
5. **Vendedores como perfil 1:1 de users** (`vendedores.user_id` unique). Login via users; dados de vendedor (telefone, comissão) na tabela própria. `comissao_pct` integer, **oculto** nos forms/tables Filament.
6. **Categorias de despesa em tabela própria** (`despesa_categorias`), seed inicial (aluguel, energia, água, internet, salários, marketing, impostos, outro). Alternativa enum fixa descartada — usuário quer adicionar novas.
7. **Roles via Shield**: `Admin` (super admin, tudo), `Vendedor` (só suas vendas; leitura de clientes e produtos; sem acesso a despesas, fornecedores, grupos, movimentações). Escopo de vendas via `modifyQueryUsing` no Resource + policy.
8. **SoftDeletes** em tabelas de cadastro e vendas (não em movimentações — histórico imutável).
9. **Filament v5**: Resources padrão, navegação agrupada (Cadastros / Operação / Financeiro).

## Risks / Trade-offs

- [Cache `estoque_qtd` divergir] → toda mutação passa por transação + movimentação; comando artisan opcional `estoque:recalcular` para reconciliar.
- [Venda concorrente esgotar estoque] → validação de saldo dentro de transação com lock (`lockForUpdate`).
- [Usuário deletado que é vendedor] → FK `user_id` com `cascadeOnDelete` no vendedor; vendas mantêm `vendedor_id` com `restrictOnDelete` (não deletar vendedor com vendas).
- [Migrations não executadas ainda] → schema revisável antes de `php artisan migrate`; nada roda automaticamente.

## Migration Plan

1. Criar migrations (ordem: users já existe → grupos, clientes, fornecedores, vendedores, produtos, movimentacoes, vendas, venda_items, despesa_categorias, despesas, spatie tables via shield).
2. Usuário revisa colunas → executa `php artisan migrate` manualmente quando aprovado.
3. Rollback: `php artisan migrate:rollback` (projeto novo, sem dados).

## Open Questions

- Formato/validação de CPF/CNPJ (máscara livre ou validação estrita?) — decidir na implementação.
- Unidade do produto: enum fixo (un, kg, lt, cx) ou texto livre?
