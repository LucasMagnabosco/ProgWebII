<?php

include_once('ProdutoDao.php');

class PostgresProdutoDao implements ProdutoDao {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function insere(Produto $produto): bool {
        $sql = "INSERT INTO produto (nome, descricao, foto) VALUES (:nome, :descricao, :foto)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nome' => $produto->getNome(),
            ':descricao' => $produto->getDescricao(),
            ':foto' => $produto->getFoto()
        ]);
    }

    public function buscaTodos(): array {
        $sql = "SELECT * FROM produto";
        $stmt = $this->conn->query($sql);
        $produtos = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = new Produto($row['nome'], $row['descricao'], $row['foto']);
        }

        return $produtos;
    }

    public function buscaPorId(int $id): ?Produto {
        $sql = "SELECT * FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Produto($row['nome'], $row['descricao'], $row['foto']);
        }

        return null;
    }
}
