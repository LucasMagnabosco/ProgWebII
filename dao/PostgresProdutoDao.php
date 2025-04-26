<?php

include_once('ProdutoDao.php');

class PostgresProdutoDao implements ProdutoDao {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function insere(Produto $produto): bool {
        $sql = "INSERT INTO produto (nome, descricao, fornecedor_id, foto) 
                VALUES (:nome, :descricao, :fornecedor_id, :foto)";
        
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':nome' => $produto->getNome(),
            ':descricao' => $produto->getDescricao(),
            ':foto' => $produto->getFoto(),
            ':fornecedor_id' => $produto->getFornecedorId()
        ]);
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
                    f.nome AS fornecedor_nome 
                FROM produto p
                JOIN fornecedor f ON p.fornecedor_id = f.id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function buscaPorId(int $id): ?Produto {
        $sql = "SELECT * FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Produto($row['nome'], $row['descricao'], $row['fornecedor_id'], $row['foto']);  // Adiciona fornecedor_id
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
        $sql = "UPDATE produto SET nome = :nome, descricao = :descricao, foto = :foto, fornecedor_id = :fornecedor_id 
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
            ':fornecedor_id' => $produto->getFornecedorId(),
            ':id' => $produto->getId()
        ]);
    }
    
}
