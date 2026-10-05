## ADDED Requirements

### Requirement: Registro de venda com itens
O sistema SHALL permitir registrar venda com cliente, vendedor, data, status (aberta/fechada/cancelada), desconto e itens (produto, quantidade, `preco_unit_int` snapshot, subtotal). `valor_total_int` SHALL ser calculado dos itens menos desconto.

#### Scenario: Venda com itens
- **WHEN** vendedor cria venda fechada com 2 produtos
- **THEN** itens gravados com preço snapshot do momento e total calculado

#### Scenario: Preço muda depois
- **WHEN** preço do produto muda após a venda
- **THEN** itens da venda mantêm o snapshot original

### Requirement: Baixa de estoque na venda
Ao criar venda fechada, o sistema SHALL, em transação única: validar saldo, decrementar `estoque_qtd` e criar movimentação tipo=saida, motivo=venda com `venda_id`.

#### Scenario: Venda fechada baixa estoque
- **WHEN** venda fechada com 2 unidades do produto X (saldo 10)
- **THEN** saldo vira 8 e existe movimentação saida/venda vinculada

#### Scenario: Estoque insuficiente
- **WHEN** venda fechada com quantidade maior que saldo
- **THEN** venda rejeitada, nenhuma alteração em estoque ou movimentações

### Requirement: Estorno no cancelamento
Ao cancelar venda fechada, o sistema SHALL devolver as quantidades ao estoque e criar movimentação tipo=entrada, motivo=estorno com `venda_id`.

#### Scenario: Cancelar venda
- **WHEN** venda fechada de 2 unidades é cancelada
- **THEN** saldo volta ao valor anterior e movimentação entrada/estorno criada

### Requirement: Venda aberta não toca estoque
Venda com status aberta SHALL NOT alterar estoque nem gerar movimentação.

#### Scenario: Venda aberta
- **WHEN** venda criada com status aberta
- **THEN** saldo do produto inalterado
