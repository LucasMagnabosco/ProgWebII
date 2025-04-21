<?php
include_once('EnderecoDao.php');
include_once('PostgresDao.php');

class PostgresEnderecoDao extends PostgresDao implements EnderecoDao {
    private $table_name = 'enderecos';
    
    public function insere($endereco) {
        $query = "INSERT INTO " . $this->table_name . 
        " (rua, numero, complemento, bairro, cep, cidade, estado) VALUES" .
        " (:rua, :numero, :complemento, :bairro, :cep, :cidade, :estado)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":rua", $endereco->getRua());
        $stmt->bindValue(":numero", $endereco->getNumero());
        $stmt->bindValue(":complemento", $endereco->getComplemento());
        $stmt->bindValue(":bairro", $endereco->getBairro());
        $stmt->bindValue(":cep", $endereco->getCep());
        $stmt->bindValue(":cidade", $endereco->getCidade());
        $stmt->bindValue(":estado", $endereco->getEstado());

        if($stmt->execute()) {
            $endereco->setId($this->conn->lastInsertId());
            return true;
        }
        return false;
    }

    public function removePorId($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function remove($endereco) {
        return $this->removePorId($endereco->getId());
    }

    public function altera($endereco) {
        $query = "UPDATE " . $this->table_name . 
        " SET rua = :rua, numero = :numero, complemento = :complemento, 
          bairro = :bairro, cep = :cep, cidade = :cidade, estado = :estado" .
        " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":rua", $endereco->getRua());
        $stmt->bindValue(":numero", $endereco->getNumero());
        $stmt->bindValue(":complemento", $endereco->getComplemento());
        $stmt->bindValue(":bairro", $endereco->getBairro());
        $stmt->bindValue(":cep", $endereco->getCep());
        $stmt->bindValue(":cidade", $endereco->getCidade());
        $stmt->bindValue(":estado", $endereco->getEstado());
        $stmt->bindValue(':id', $endereco->getId());

        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            return $this->criarEndereco($row);
        }
        return null;
    }

    public function buscaTodos() {
        $enderecos = array();

        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $enderecos[] = $this->criarEndereco($row);
        }

        return $enderecos;
    }

    private function criarEndereco($row) {
        $endereco = new Endereco(
            $row['rua'],
            $row['numero'],
            $row['complemento'],
            $row['bairro'],
            $row['cep'],
            $row['cidade'],
            $row['estado']
        );
        $endereco->setId($row['id']);
        return $endereco;
    }
}
?> 