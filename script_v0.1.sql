ALTER TABLE usuario ADD COLUMN is_admin BOOLEAN NOT NULL DEFAULT FALSE;

-- Inserindo o primeiro usuário administrador
INSERT INTO usuario (nome, email, senha, telefone, tipo, is_admin) 
VALUES ('Administrador', 'admin@sistema.com', '202cb962ac59075b964b07152d234b70', '(11) 99999-9999', FALSE, TRUE);

