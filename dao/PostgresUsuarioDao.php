<?php

include_once('UsuarioDao.php');
include_once('PostgresDao.php');
include_once(dirname(__FILE__) . '/../model/Usuario.php');
include_once('PostgresEnderecoDao.php');

class PostgresUsuarioDao extends PostgresDao implements UsuarioDao
{

    private $table_name = 'usuario';

    public function insere($usuario)
    {
        $query = "INSERT INTO " . $this->table_name .
            " (nome, email, senha, telefone, endereco_id, tipo, cartao_credito) VALUES" .
            " (:nome, :email, :senha, :telefone, :endereco_id, :tipo, :cartao_credito)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $usuario->getNome());
        $stmt->bindValue(":email", $usuario->getEmail());
        $stmt->bindValue(":senha", $usuario->getSenha());
        $stmt->bindValue(":telefone", $usuario->getTelefone());

        $endereco = $usuario->getEndereco();
        $endereco_id = $endereco ? $endereco->getId() : null;
        $stmt->bindValue(":endereco_id", $endereco_id);

        $tipo = $usuario->getTipo() ? 'true' : 'false';
        $stmt->bindValue(":tipo", $tipo, PDO::PARAM_BOOL);
        $stmt->bindValue(":cartao_credito", $usuario->getCartaoCredito());

        return $stmt->execute();
    }

    public function removePorId($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function remove($usuario)
    {
        return $this->removePorId($usuario->getId());
    }

    public function altera($usuario)
    {
        $query = "UPDATE " . $this->table_name .
            " SET nome = :nome, email = :email, senha = :senha, 
          telefone = :telefone, endereco_id = :endereco_id,
          tipo = :tipo, cartao_credito = :cartao_credito" .
            " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $usuario->getNome());
        $stmt->bindValue(":email", $usuario->getEmail());
        $stmt->bindValue(":senha", $usuario->getSenha());
        $stmt->bindValue(":telefone", $usuario->getTelefone());

        $endereco = $usuario->getEndereco();
        $endereco_id = $endereco ? $endereco->getId() : null;
        $stmt->bindValue(":endereco_id", $endereco_id);

        $stmt->bindValue(":tipo", $usuario->getTipo());
        $stmt->bindValue(":cartao_credito", $usuario->getCartaoCredito());
        $stmt->bindValue(':id', $usuario->getId());

        return $stmt->execute();
    }

    public function buscaPorId($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->criarUsuario($row);
        }
        return null;
    }

    public function buscaPorEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->criarUsuario($row);
        }
        return null;
    }

    public function buscaTodos()
    {
        $usuarios = array();

        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $this->criarUsuario($row);
        }

        return $usuarios;
    }

    public function buscaPorTipo($tipo)
    {
        $usuarios = array();
        $query = "SELECT * FROM " . $this->table_name . " WHERE tipo = :tipo ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $this->criarUsuario($row);
        }

        return $usuarios;
    }

    public function atualizarTipo($usuario, $novoTipo)
    {
        $query = "UPDATE " . $this->table_name . " SET tipo = :tipo WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':tipo', $novoTipo);
        $stmt->bindValue(':id', $usuario->getId());

        if ($stmt->execute()) {
            $usuario->setTipo($novoTipo);
            return true;
        }
        return false;
    }

    private function criarUsuario($row)
    {
        $endereco = null;
        if ($row['endereco_id']) {
            $enderecoDao = new PostgresEnderecoDao($this->conn);
            $endereco = $enderecoDao->buscaPorId($row['endereco_id']);
        }

        $usuario = new Usuario(
            $row['nome'],
            $row['email'],
            $row['senha'],
            $row['telefone'],
            $row['cartao_credito'],
            $endereco
        );
        $usuario->setId($row['id']);
        $usuario->setTipo($row['tipo']);
        return $usuario;
    }

    public function atualizarEndereco($usuario, $endereco)
    {
        // Primeiro insere/atualiza o endereço
        $enderecoDao = new PostgresEnderecoDao($this->conn);
        if (!$endereco->getId()) {
            $enderecoDao->insere($endereco);
        } else {
            $enderecoDao->altera($endereco);
        }

        // Atualiza o usuário com o novo endereço_id
        $query = "UPDATE " . $this->table_name . " SET endereco_id = :endereco_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':endereco_id', $endereco->getId());
        $stmt->bindValue(':id', $usuario->getId());

        if ($stmt->execute()) {
            $usuario->adicionarEndereco($endereco);
            return true;
        }
        return false;
    }

}
?>