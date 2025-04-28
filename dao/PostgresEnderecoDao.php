<?php
include_once('EnderecoDao.php');
include_once('PostgresDao.php');
include_once(dirname(__FILE__) . '/../model/Endereco.php');

class PostgresEnderecoDao extends PostgresDao implements EnderecoDao {
    private $table_name = 'endereco';
    
    public function insere($endereco) {
        $query = "INSERT INTO " . $this->table_name . 
        " (rua, numero, complemento, bairro, cidade, estado, cep) VALUES" .
        " (:rua, :numero, :complemento, :bairro, :cidade, :estado, :cep)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":rua", $endereco->getRua());
        $stmt->bindValue(":numero", $endereco->getNumero());
        $stmt->bindValue(":complemento", $endereco->getComplemento());
        $stmt->bindValue(":bairro", $endereco->getBairro());
        $stmt->bindValue(":cidade", $endereco->getCidade());
        $stmt->bindValue(":estado", $endereco->getEstado());
        $stmt->bindValue(":cep", $endereco->getCep());

        if ($stmt->execute()) {
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
          bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep" .
        " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":rua", $endereco->getRua());
        $stmt->bindValue(":numero", $endereco->getNumero());
        $stmt->bindValue(":complemento", $endereco->getComplemento());
        $stmt->bindValue(":bairro", $endereco->getBairro());
        $stmt->bindValue(":cidade", $endereco->getCidade());
        $stmt->bindValue(":estado", $endereco->getEstado());
        $stmt->bindValue(":cep", $endereco->getCep());
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
            $row['cidade'],
            $row['estado'],
            $row['cep']
        );
        $endereco->setId($row['id']);
        return $endereco;
    }
}
?> 