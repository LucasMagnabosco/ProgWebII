<?php

include_once('ProdutoDao.php');

class PostgresProdutoDao implements ProdutoDao {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function insere(Produto $produto): bool {
        try {
            $sql = "INSERT INTO produto (nome, descricao, fornecedor_id, quantidade, preco, foto) 
                    VALUES (:nome, :descricao, :fornecedor_id, :quantidade, :preco, :foto)";
            
            $stmt = $this->conn->prepare($sql);
            
            $params = [
                ':nome' => $produto->getNome(),
                ':descricao' => $produto->getDescricao(),
                ':foto' => $produto->getFoto(),
                ':fornecedor_id' => $produto->getFornecedorId(),
                ':quantidade' => $produto->getQuantidade(),
                ':preco' => $produto->getPreco()
            ];

            error_log("Tentando inserir produto com parâmetros: " . print_r($params, true));
            
            $result = $stmt->execute($params);
            
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
    

    // public function buscaTodos(): array {
    //     $sql = "SELECT * FROM produto";
    //     $stmt = $this->conn->query($sql);
    //     $produtos = [];

    //     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         $produtos[] = new Produto($row['nome'], $row['descricao'], $row['foto']);
    //     }

    //     return $produtos;
    // }

    public function buscaTodos(): array {
        $sql = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, 
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
            return new Produto($row['nome'], $row['descricao'], $row['fornecedor_id'], $row['preco'], $row['quantidade'], $row['foto']);
        }

        return null;
    }

    public function remove(int $id): bool {
        $sql = "DELETE FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Método para atualizar um produto
    public function atualiza(Produto $produto): bool {
        var_dump($produto);
        $sql = "UPDATE produto SET nome = :nome, descricao = :descricao, foto = :foto,
                preco = :preco, quantidade = :quantidade
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        // var_dump($stmt->execute([
        //     ':nome' => $produto->getNome(),
        //     ':descricao' => $produto->getDescricao(),
        //     ':foto' => $produto->getFoto(),
        //     ':fornecedor_id' => $produto->getFornecedorId(),
        //     ':id' => $produto->getId()
        // ]));
        return $stmt->execute([
            ':nome' => $produto->getNome(),
            ':descricao' => $produto->getDescricao(),
            ':foto' => $produto->getFoto(),
            ':id' => $produto->getId(),
            ':preco' => $produto->getPreco(),
            ':quantidade' => $produto->getQuantidade()
        ]);
    }
    
    public function buscaPorFornecedor(int $fornecedorId): array {
        $sql = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, 
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
