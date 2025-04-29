<?php

include_once('FornecedorDao.php');
include_once('PostgresDao.php');
include_once(dirname(__FILE__) . '/../model/Fornecedor.php');

class PostgresFornecedorDao extends PostgresDao implements FornecedorDao {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function buscaTodos(): array {
        $sql = "SELECT * FROM fornecedor";
        $stmt = $this->conn->query($sql);
        $fornecedores = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Criação de objetos Fornecedor a partir dos dados retornados
            $fornecedor = new Fornecedor(
                $row['id'],
                $row['nome'],
                $row['cnpj'],
                $row['telefone'],
                $row['email'],
                $row['endereco_id'],
                $row['descricao']
            );
            $fornecedores[] = $fornecedor;
        }
    
        return $fornecedores;  // Retorna um array de objetos Fornecedor
    }

    public function buscaPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM fornecedor WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Fornecedor(
                $row['id'],
                $row['nome'],
                $row['cnpj'],
                $row['telefone'],
                $row['email'],
                $row['endereco_id'],
                $row['descricao']
            );
        }
        return null;
    }

    public function insere($fornecedor) {
        $stmt = $this->conn->prepare("INSERT INTO fornecedor (nome, cnpj, telefone, email, endereco_id, descricao) 
                                    VALUES (:nome, :cnpj, :telefone, :email, :endereco_id, :descricao)");
        
        $stmt->bindParam(":nome", $fornecedor->getNome());
        $stmt->bindParam(":cnpj", $fornecedor->getCnpj());
        $stmt->bindParam(":telefone", $fornecedor->getTelefone());
        $stmt->bindParam(":email", $fornecedor->getEmail());
        $stmt->bindParam(":endereco_id", $fornecedor->getEnderecoId());
        $stmt->bindParam(":descricao", $fornecedor->getDescricao());
        
        return $stmt->execute();
    }

    public function atualiza($fornecedor) {
        $stmt = $this->conn->prepare("UPDATE fornecedor 
                                    SET nome = :nome, 
                                        cnpj = :cnpj, 
                                        telefone = :telefone, 
                                        email = :email, 
                                        endereco_id = :endereco_id,
                                        descricao = :descricao 
                                    WHERE id = :id");
        
        $stmt->bindParam(":nome", $fornecedor->getNome());
        $stmt->bindParam(":cnpj", $fornecedor->getCnpj());
        $stmt->bindParam(":telefone", $fornecedor->getTelefone());
        $stmt->bindParam(":email", $fornecedor->getEmail());
        $stmt->bindParam(":endereco_id", $fornecedor->getEnderecoId());
        $stmt->bindParam(":descricao", $fornecedor->getDescricao());
        $stmt->bindParam(":id", $fornecedor->getId());
        
        return $stmt->execute();
    }

    public function deleta($id) {
        $stmt = $this->conn->prepare("DELETE FROM fornecedor WHERE id = :id");
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
