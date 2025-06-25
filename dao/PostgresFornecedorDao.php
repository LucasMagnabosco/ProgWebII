<?php

include_once('FornecedorDao.php');
include_once('PostgresDao.php');
include_once(dirname(__FILE__) . '/../model/Fornecedor.php');
include_once('PostgresUsuarioDao.php');

class PostgresFornecedorDao extends PostgresDao implements FornecedorDao {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function buscaTodos(): array {
        $sql = "SELECT f.*, u.* FROM fornecedor f 
                JOIN usuario u ON f.usuario_id = u.id";
        $stmt = $this->conn->query($sql);
        $fornecedores = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = $this->criarFornecedor($row);
        }
    
        return $fornecedores;
    }

    public function buscaPorId($id) {
        $sql = "SELECT f.*, u.* FROM fornecedor f 
                JOIN usuario u ON f.usuario_id = u.id 
                WHERE f.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->criarFornecedor($row);
        }
        return null;
    }

    public function buscaPorCnpj($cnpj) {
        $sql = "SELECT f.*, u.* FROM fornecedor f 
                JOIN usuario u ON f.usuario_id = u.id 
                WHERE f.cnpj = :cnpj";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":cnpj", $cnpj);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->criarFornecedor($row);
        }
        return null;
    }

    public function buscaPorUsuarioId($usuarioId) {
        $sql = "SELECT f.id as fornecedor_id, f.*, u.* 
                FROM fornecedor f 
                JOIN usuario u ON f.usuario_id = u.id 
                WHERE u.id = :usuario_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":usuario_id", $usuarioId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->criarFornecedor($row);
        }
        return null;
    }

    public function insere($fornecedor) {
        try {
            $this->conn->beginTransaction();

            // Primeiro insere o usuário
            $usuarioDao = new PostgresUsuarioDao($this->conn);
            // Define o tipo como true para fornecedor
            $fornecedor->setTipo(true);
            if (!$usuarioDao->insere($fornecedor)) {
                throw new Exception("Erro ao inserir usuário");
            }

            // Pega o ID do usuário inserido
            $usuarioId = $this->conn->lastInsertId();

            // Insere o fornecedor
            $sql = "INSERT INTO fornecedor (usuario_id, cnpj, descricao) 
                    VALUES (:usuario_id, :cnpj, :descricao)";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(":usuario_id", $usuarioId);
            $stmt->bindParam(":cnpj", $fornecedor->getCnpj());
            $stmt->bindParam(":descricao", $fornecedor->getDescricao());
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir fornecedor");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function atualiza($fornecedor) {
        try {
            $this->conn->beginTransaction();

            // Atualiza o usuário
            $usuarioDao = new PostgresUsuarioDao($this->conn);
            if (!$usuarioDao->altera($fornecedor)) {
                throw new Exception("Erro ao atualizar usuário");
            }

            // Atualiza o fornecedor
            $sql = "UPDATE fornecedor 
                    SET cnpj = :cnpj, descricao = :descricao 
                    WHERE usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(":cnpj", $fornecedor->getCnpj());
            $stmt->bindValue(":descricao", $fornecedor->getDescricao());
            $stmt->bindValue(":usuario_id", $fornecedor->getId(), PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar fornecedor");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function deleta($id) {
        try {
            $this->conn->beginTransaction();

            $sql = "DELETE FROM fornecedor WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao deletar fornecedor");
            }

            // O usuário será deletado automaticamente pelo ON DELETE CASCADE

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    private function criarFornecedor($row) {
        $fornecedor = new Fornecedor(
            $row['nome'],
            $row['email'],
            $row['senha'],
            $row['telefone'],
            $row['cnpj'],
            $row['descricao']
        );
        
        // ID do usuário
        $fornecedor->setId($row['id']);
        
        // ID do fornecedor (usando o alias fornecedor_id)
        if (isset($row['fornecedor_id'])) {
            $fornecedor->setFornecedorId($row['fornecedor_id']);
        }
        
        return $fornecedor;
    }
}
?>
