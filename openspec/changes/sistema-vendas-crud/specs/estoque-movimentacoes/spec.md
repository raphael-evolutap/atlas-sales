## ADDED Requirements

### Requirement: Registro de movimentações
O sistema SHALL registrar movimentações de estoque com produto, tipo (entrada/saida/ajuste), quantidade, motivo (compra/venda/estorno/ajuste/perda), venda opcional, observações e usuário responsável. Movimentações SHALL ser imutáveis (sem softdelete/edição).

#### Scenario: Entrada manual
- **WHEN** Admin registra movimentação tipo=entrada, motivo=compra, quantidade=50
- **THEN** movimentação gravada com user_id do Admin e `produtos.estoque_qtd` incrementado

#### Scenario: Ajuste negativo
- **WHEN** Admin registra ajuste de -3 (perda)
- **THEN** cache do produto decrementado em 3

### Requirement: Cache de saldo no produto
`produtos.estoque_qtd` SHALL refletir o saldo calculado das movimentações; fonte de verdade = movimentações.

#### Scenario: Consistência cache
- **WHEN** qualquer movimentação é criada
- **THEN** `estoque_qtd` do produto é atualizado na mesma transação

### Requirement: Validação de saldo
O sistema SHALL impedir movimentação de saída que deixe o saldo negativo.

#### Scenario: Saída sem saldo
- **WHEN** saída de quantidade maior que saldo atual
- **THEN** operação rejeitada com erro e nenhuma alteração gravada
