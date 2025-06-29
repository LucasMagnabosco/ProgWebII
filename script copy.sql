-- =====================================================
-- SCRIPT COMPLETO DO SISTEMA - TODAS AS VERSÕES COMBINADAS
-- =====================================================

-- =====================================================
-- 1. CRIAÇÃO DAS TABELAS PRINCIPAIS
-- =====================================================

CREATE TABLE endereco (
    id SERIAL PRIMARY KEY,
    rua VARCHAR(255) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100),
    bairro VARCHAR(100) NOT NULL,
    cep VARCHAR(9) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL
);

CREATE TABLE usuario (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    endereco_id INT,
    tipo BOOLEAN NOT NULL DEFAULT FALSE,
    cartao_credito VARCHAR(19),
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (endereco_id) REFERENCES endereco(id)
);

CREATE TABLE fornecedor (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    cnpj VARCHAR(20) NOT NULL UNIQUE,
    descricao TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE produto (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    foto BYTEA,
    quantidade INT NOT NULL DEFAULT 0,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00 CHECK (preco >= 0),
    fornecedor_id INT NOT NULL,
    codigo VARCHAR(50),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(id)
);

CREATE TABLE estoque (
    id SERIAL PRIMARY KEY,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (produto_id) REFERENCES produto(id)
);

-- =====================================================
-- 2. CRIAÇÃO DAS TABELAS DE PEDIDOS
-- =====================================================

CREATE TABLE pedido (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER NOT NULL,
    data_pedido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDENTE',
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    endereco_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id),
    FOREIGN KEY (endereco_id) REFERENCES endereco(id)
);

CREATE TABLE pedido_fornecedor (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER NOT NULL,
    fornecedor_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDENTE',
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    data_subpedido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_envio TIMESTAMP NULL,
    data_cancelamento TIMESTAMP NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(id)
);

CREATE TABLE itens_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER NOT NULL,
    produto_id INTEGER NOT NULL,
    quantidade INTEGER NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    pedido_fornecedor_id INTEGER,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produto(id),
    FOREIGN KEY (pedido_fornecedor_id) REFERENCES pedido_fornecedor(id) ON DELETE CASCADE
);

-- =====================================================
-- 3. ÍNDICES PARA OTIMIZAÇÃO
-- =====================================================

-- Índice para otimizar buscas por código e fornecedor
CREATE INDEX idx_produto_codigo_fornecedor ON produto(codigo, fornecedor_id);

-- Índices para pedidos
CREATE INDEX idx_pedido_usuario ON pedido(usuario_id);
CREATE INDEX idx_pedido_status ON pedido(status);
CREATE INDEX idx_itens_pedido_pedido ON itens_pedido(pedido_id);
CREATE INDEX idx_itens_pedido_produto ON itens_pedido(produto_id);

-- =====================================================
-- 4. INSERÇÃO DE DADOS DE EXEMPLO
-- =====================================================

-- Inserção de dados de exemplo
-- Todas senhas são 123 (MD5: 202cb962ac59075b964b07152d234b70)

-- Usuários fornecedores
--
--Todas senhas são 123
--Todas senhas são 123
--Todas senhas são 123
--
INSERT INTO usuario (nome, email, senha, telefone, tipo) VALUES
('Eletrônicos', 'eletro@fornecedor.com', '202cb962ac59075b964b07152d234b70', '(11) 99999-1111', true),
('Roupas', 'roupas@fornecedor.com', '202cb962ac59075b964b07152d234b70', '(11) 99999-2222', true),
('Alimentos', 'alimentos@fornecedor.com', '202cb962ac59075b964b07152d234b70', '(11) 99999-3333', true),
('Movéis', 'moveis@fornecedor.com', '202cb962ac59075b964b07152d234b70', '(11) 99999-4444', true),
('Livros', 'livros@fornecedor.com', '202cb962ac59075b964b07152d234b70', '(11) 99999-5555', true);

-- Fornecedores
INSERT INTO fornecedor (usuario_id, cnpj, descricao) VALUES
(1, '12.345.678/0001-01', 'Fornecedor de Eletrônicos'),
(2, '23.456.789/0001-02', 'Fornecedor de Roupas'),
(3, '34.567.890/0001-03', 'Fornecedor de Alimentos'),
(4, '45.678.901/0001-04', 'Fornecedor de Móveis'),
(5, '56.789.012/0001-05', 'Fornecedor de Livros');

-- Produtos do Fornecedor 1 (Eletrônicos)
INSERT INTO produto (nome, descricao, fornecedor_id, preco, quantidade, codigo) VALUES
('Smartphone XYZ', 'Smartphone último modelo com 128GB', 1, 1999.99, 50, 'ELET001'),
('Notebook ABC', 'Notebook i5 8GB RAM 256GB SSD', 1, 3499.99, 30, 'ELET002'),
('Tablet 10"', 'Tablet com tela de 10 polegadas', 1, 899.99, 40, 'ELET003'),
('Smart TV 50"', 'Smart TV 4K 50 polegadas', 1, 2499.99, 20, 'ELET004'),
('Fone Bluetooth', 'Fone de ouvido sem fio', 1, 199.99, 100, 'ELET005');

-- Produtos do Fornecedor 2 (Roupas)
INSERT INTO produto (nome, descricao, fornecedor_id, preco, quantidade, codigo) VALUES
('Camiseta Básica', 'Camiseta 100% algodão', 2, 49.99, 200, 'ROUP001'),
('Calça Jeans', 'Calça jeans slim fit', 2, 129.99, 150, 'ROUP002'),
('Vestido Floral', 'Vestido estampado floral', 2, 159.99, 100, 'ROUP003'),
('Jaqueta Couro', 'Jaqueta de couro sintético', 2, 299.99, 50, 'ROUP004'),
('Tênis Casual', 'Tênis casual confortável', 2, 199.99, 80, 'ROUP005');

-- Produtos do Fornecedor 3 (Alimentos)
INSERT INTO produto (nome, descricao, fornecedor_id, preco, quantidade, codigo) VALUES
('Arroz Integral', 'Arroz integral tipo 1', 3, 8.99, 500, 'ALIM001'),
('Feijão Carioca', 'Feijão carioca tipo 1', 3, 7.99, 500, 'ALIM002'),
('Azeite Extra Virgem', 'Azeite de oliva extra virgem', 3, 29.99, 200, 'ALIM003'),
('Café Premium', 'Café em grãos premium', 3, 19.99, 300, 'ALIM004'),
('Farinha de Trigo', 'Farinha de trigo especial', 3, 5.99, 400, 'ALIM005');

-- Produtos do Fornecedor 4 (Móveis)
INSERT INTO produto (nome, descricao, fornecedor_id, preco, quantidade, codigo) VALUES
('Sofá 3 Lugares', 'Sofá retrátil 3 lugares', 4, 1999.99, 20, 'MOVE001'),
('Mesa de Jantar', 'Mesa de jantar 6 lugares', 4, 1499.99, 15, 'MOVE002'),
('Cama Box', 'Cama box casal com baú', 4, 1299.99, 25, 'MOVE003'),
('Guarda-Roupa', 'Guarda-roupa 6 portas', 4, 2499.99, 10, 'MOVE004'),
('Rack TV', 'Rack para TV até 55"', 4, 599.99, 30, 'MOVE005');

-- Produtos do Fornecedor 5 (Livros)
INSERT INTO produto (nome, descricao, fornecedor_id, preco, quantidade, codigo) VALUES
('Dom Casmurro', 'Romance de Machado de Assis', 5, 29.99, 100, 'LIVR001'),
('O Pequeno Príncipe', 'Clássico da literatura', 5, 24.99, 150, 'LIVR002'),
('1984', 'Romance distópico de George Orwell', 5, 34.99, 80, 'LIVR003'),
('A Arte da Guerra', 'Tratado militar de Sun Tzu', 5, 19.99, 120, 'LIVR004'),
('O Hobbit', 'Fantasia de J.R.R. Tolkien', 5, 39.99, 90, 'LIVR005');

-- =====================================================
-- 5. INSERÇÃO DO USUÁRIO ADMINISTRADOR
-- =====================================================

-- Inserindo o primeiro usuário administrador
INSERT INTO usuario (nome, email, senha, telefone, tipo, is_admin) 
VALUES ('Administrador', 'admin@sistema.com', '202cb962ac59075b964b07152d234b70', '(11) 99999-9999', FALSE, TRUE);

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================