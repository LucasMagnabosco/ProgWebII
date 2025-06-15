ALTER TABLE pedido ADD COLUMN endereco_id INT;

UPDATE pedido SET endereco_id = (SELECT endereco_id FROM usuario WHERE id = pedido.usuario_id);

ALTER TABLE pedido ALTER COLUMN endereco_id SET NOT NULL;

ALTER TABLE pedido ADD CONSTRAINT fk_pedido_endereco FOREIGN KEY (endereco_id) REFERENCES endereco(id);
