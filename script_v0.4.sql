CREATE TABLE pedido_fornecedor (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER NOT NULL,
    fornecedor_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDENTE',
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    data_subpedido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(id)
);

ALTER TABLE itens_pedido ADD COLUMN pedido_fornecedor_id INTEGER;
ALTER TABLE itens_pedido ALTER COLUMN pedido_fornecedor_id SET NOT NULL;
ALTER TABLE itens_pedido ADD CONSTRAINT fk_itens_pedido_fornecedor FOREIGN KEY (pedido_fornecedor_id) REFERENCES pedido_fornecedor(id) ON DELETE CASCADE;