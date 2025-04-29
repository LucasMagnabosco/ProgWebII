<?php

include_once('FornecedorDao.php');
include_once('PostgresDao.php');
include_once(dirname(__FILE__) . '/../model/Fornecedor.php');

class PostgresFornecedorDao extends PostgresDao implements FornecedorDao {

    private PDO $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function buscaTodos(): array {

        $sql = "SELECT * FROM fornecedor";
        $stmt = $this->pdo->query($sql);
        $fornecedores = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Criação de objetos Fornecedor a partir dos dados retornados
            $fornecedor = new Fornecedor(
                $row['id'],
                $row['nome'],
                $row['cnpj'],
                $row['telefone'],
                $row['email'],
                $row['endereco_id']
            );
            $fornecedores[] = $fornecedor;
        }
    
        return $fornecedores;  // Retorna um array de objetos Fornecedor
    }
    

    public function buscaPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedor WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insere($fornecedor) {
        $stmt = $this->pdo->prepare("INSERT INTO fornecedor (nome) VALUES (:nome)");
        $stmt->bindParam(":nome", $fornecedor['nome']);
        return $stmt->execute();
    }

    public function atualiza($fornecedor) {
        $stmt = $this->pdo->prepare("UPDATE fornecedor SET nome = :nome WHERE id = :id");
        $stmt->bindParam(":nome", $fornecedor['nome']);
        $stmt->bindParam(":id", $fornecedor['id']);
        return $stmt->execute();
    }

    public function deleta($id) {
        $stmt = $this->pdo->prepare("DELETE FROM fornecedor WHERE id = :id");
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
