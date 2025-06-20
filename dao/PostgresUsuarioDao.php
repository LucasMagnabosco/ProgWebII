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
            " (nome, email, senha, telefone, endereco_id, tipo, cartao_credito, is_admin) VALUES" .
            " (:nome, :email, :senha, :telefone, :endereco_id, :tipo, :cartao_credito, :is_admin)";

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
        $stmt->bindValue(":is_admin", $usuario->isAdmin(), PDO::PARAM_BOOL);

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
          tipo = :tipo, cartao_credito = :cartao_credito, is_admin = :is_admin" .
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
        $stmt->bindValue(":is_admin", $usuario->isAdmin(), PDO::PARAM_BOOL);
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
        $usuario->setIsAdmin($row['is_admin']);
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

    public function atualizarStatusAdmin($usuario, $isAdmin)
    {
        $query = "UPDATE " . $this->table_name . " SET is_admin = :is_admin WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':is_admin', $isAdmin, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $usuario->getId());

        if ($stmt->execute()) {
            $usuario->setIsAdmin($isAdmin);
            return true;
        }
        return false;
    }

    public function buscaTodosPaginado($inicio, $quantos, $termo = '') {
        $query = "SELECT * FROM " . $this->table_name;
        
        if (!empty($termo)) {
            $query .= " WHERE UPPER(nome) LIKE :termo 
                       OR UPPER(email) LIKE :termo";
        }
        
        $query .= " ORDER BY id ASC
                   LIMIT :limit OFFSET :offset";
     
        $stmt = $this->conn->prepare($query);
        
        if (!empty($termo)) {
            $stmt->bindValue(':termo', '%' . strtoupper($termo) . '%');
        }
        
        $stmt->bindValue(':limit', $quantos, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $inicio, PDO::PARAM_INT);
        $stmt->execute();

        $usuarios = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $this->criarUsuario($row);
        }
        return $usuarios;
    }

    public function buscaTodosFormatados($inicio, $quantos, $termo = '')
    {
        $usuarios = $this->buscaTodosPaginado($inicio, $quantos, $termo);
        $usuariosJSON = [];
        foreach ($usuarios as $usuario) {
            $usuariosJSON[] = $usuario->toJson();
        }
        return json_encode($usuariosJSON, JSON_PRETTY_PRINT);
    }

    public function buscaFiltrada($nome, $inicio, $quantos)
    {
        $usuarios = $this->buscaTodosPaginado($inicio, $quantos, $nome);
        $usuariosJSON = [];
        foreach ($usuarios as $usuario) {
            $usuariosJSON[] = $usuario->toJson();
        }
        return $usuariosJSON;
    }

    public function contaTodos() {
        $quantos = 0;
        $query = "SELECT COUNT(*) AS contagem FROM " . $this->table_name;
     
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $quantos = $contagem;
        }
        
        return $quantos;
    }

    public function contaComNome($nome) {
        $quantos = 0;
        $query = "SELECT COUNT(*) AS contagem FROM " . $this->table_name . "
                WHERE UPPER(nome) LIKE :termo 
                OR UPPER(email) LIKE :termo";
     
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':termo', '%' . strtoupper($nome) . '%');
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $quantos = $contagem;
        }
        
        return $quantos;
    }

}
?>