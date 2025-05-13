<?php

include_once('ProdutoDao.php');

class PostgresProdutoDao implements ProdutoDao {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function insere(Produto $produto): bool {
    try {
        $sql = "INSERT INTO produto (nome, descricao, fornecedor_id, quantidade, preco, foto, codigo) 
                VALUES (:nome, :descricao, :fornecedor_id, :quantidade, :preco, :foto, :codigo)";
        
        $stmt = $this->conn->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindValue(':nome', $produto->getNome());
        $stmt->bindValue(':descricao', $produto->getDescricao());
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId(), PDO::PARAM_INT);
        $stmt->bindValue(':quantidade', $produto->getQuantidade(), PDO::PARAM_INT);
        $stmt->bindValue(':preco', $produto->getPreco());
        $stmt->bindValue(':codigo', $produto->getCodigo());

        // Tratamento especial para imagem como BYTEA (binário)
        $stmt->bindValue(':foto', $produto->getFoto(), PDO::PARAM_LOB);

        $result = $stmt->execute();

        if (!$result) {
            error_log("Erro ao inserir produto: " . print_r($stmt->errorInfo(), true));
            return false;
        }

        return true;

    } catch (PDOException $e) {
        error_log("Exceção ao inserir produto: " . $e->getMessage());
        return false;
    }
}

    

    public function buscaTodos(): array {
        $sql = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    u.nome AS fornecedor_nome 
                FROM produto p
                JOIN fornecedor f ON p.fornecedor_id = f.id
                JOIN usuario u ON f.usuario_id = u.id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function buscaPorId(int $id): ?Produto {
        $sql = "SELECT * FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Produto(
                $row['nome'], 
                $row['descricao'], 
                $row['fornecedor_id'], 
                $row['preco'], 
                $row['quantidade'], 
                $row['foto'],
                $row['id'],
                $row['codigo']
            );
        }

        return null;
    }

    public function buscaPorCodigo(string $codigo, int $fornecedorId): ?Produto {
        $sql = "SELECT * FROM produto WHERE codigo = :codigo AND fornecedor_id = :fornecedor_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo,
            ':fornecedor_id' => $fornecedorId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Produto(
                $row['nome'], 
                $row['descricao'], 
                $row['fornecedor_id'], 
                $row['preco'], 
                $row['quantidade'], 
                $row['foto'],
                $row['id'],
                $row['codigo']
            );
        }

        return null;
    }

    public function remove(int $id): bool {
        $sql = "DELETE FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function atualiza(Produto $produto): bool {
    // Verifica se a foto foi fornecida
    $temFoto = !is_null($produto->getFoto());

    // Monta SQL dinamicamente
    $sql = "UPDATE produto SET 
                nome = :nome, 
                descricao = :descricao, 
                preco = :preco, 
                quantidade = :quantidade, 
                codigo = :codigo";

    if ($temFoto) {
        $sql .= ", foto = :foto";
    }

    $sql .= " WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    // Bind dos parâmetros obrigatórios
    $stmt->bindValue(':nome', $produto->getNome());
    $stmt->bindValue(':descricao', $produto->getDescricao());
    $stmt->bindValue(':preco', $produto->getPreco());
    $stmt->bindValue(':quantidade', $produto->getQuantidade(), PDO::PARAM_INT);
    $stmt->bindValue(':codigo', $produto->getCodigo());
    $stmt->bindValue(':id', $produto->getId(), PDO::PARAM_INT);

    // Bind da foto apenas se existir
    if ($temFoto) {
        $stmt->bindValue(':foto', $produto->getFoto(), PDO::PARAM_LOB);
    }

    return $stmt->execute();
}

    
    public function buscaPorFornecedor(int $fornecedorId): array {
        $sql = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    u.nome AS fornecedor_nome 
                FROM produto p
                JOIN fornecedor f ON p.fornecedor_id = f.id
                JOIN usuario u ON f.usuario_id = u.id
                WHERE p.fornecedor_id = :fornecedor_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':fornecedor_id' => $fornecedorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
