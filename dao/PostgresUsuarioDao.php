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

        // CORREÇÃO: Usa PDO::PARAM_BOOL em vez de PDO::PARAM_INT para boolean
        $stmt->bindValue(":tipo", $usuario->getTipo(), PDO::PARAM_BOOL);
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
        $stmt->bindValue(":telefone", $usuario->getTelefone() ?: null);

        $endereco = $usuario->getEndereco();
        $endereco_id = $endereco ? $endereco->getId() : null;
        $stmt->bindValue(":endereco_id", $endereco_id, PDO::PARAM_INT);

        // CORREÇÃO: Usa PDO::PARAM_BOOL em vez de PDO::PARAM_INT para boolean
        $stmt->bindValue(":tipo", $usuario->getTipo(), PDO::PARAM_BOOL);
        
        // Trata cartao_credito null
        $cartaoCredito = $usuario->getCartaoCredito();
        $stmt->bindValue(":cartao_credito", $cartaoCredito ?: null);
        
        $stmt->bindValue(":is_admin", $usuario->isAdmin(), PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function alteraDadosBasicos($usuario)
    {
        $query = "UPDATE " . $this->table_name .
            " SET nome = :nome, email = :email, senha = :senha, 
          telefone = :telefone, endereco_id = :endereco_id,
          tipo = :tipo, cartao_credito = :cartao_credito" .
            " WHERE id = :id";

        error_log("DEBUG: Query SQL = " . $query);
        error_log("DEBUG: ID do usuário = " . $usuario->getId());

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $usuario->getNome());
        $stmt->bindValue(":email", $usuario->getEmail());
        $stmt->bindValue(":senha", $usuario->getSenha());
        $stmt->bindValue(":telefone", $usuario->getTelefone() ?: null);

        $endereco = $usuario->getEndereco();
        $endereco_id = $endereco ? $endereco->getId() : null;
        $stmt->bindValue(":endereco_id", $endereco_id, PDO::PARAM_INT);

        // CORREÇÃO: Usa PDO::PARAM_BOOL em vez de PDO::PARAM_INT para boolean
        $tipoValue = $usuario->getTipo();
        $stmt->bindValue(":tipo", $tipoValue, PDO::PARAM_BOOL);
        
        error_log("DEBUG: Tipo sendo salvo = " . ($tipoValue ? 'true' : 'false') . " (tipo: " . gettype($tipoValue) . ")");
        error_log("DEBUG: Tipo original do usuário = " . ($usuario->getTipo() ? 'true' : 'false') . " (tipo: " . gettype($usuario->getTipo()) . ")");
        
        // Trata cartao_credito null
        $cartaoCredito = $usuario->getCartaoCredito();
        $stmt->bindValue(":cartao_credito", $cartaoCredito ?: null);
        
        $stmt->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);

        // Log de todos os valores antes do UPDATE
        error_log("DEBUG: === VALORES ANTES DO UPDATE ===");
        error_log("DEBUG: nome = '" . $usuario->getNome() . "'");
        error_log("DEBUG: email = '" . $usuario->getEmail() . "'");
        error_log("DEBUG: senha = '" . $usuario->getSenha() . "'");
        error_log("DEBUG: telefone = '" . ($usuario->getTelefone() ?: 'null') . "'");
        error_log("DEBUG: endereco_id = " . ($endereco_id ?: 'null'));
        error_log("DEBUG: tipo = " . ($tipoValue ? 'true' : 'false') . " (tipo: " . gettype($tipoValue) . ")");
        error_log("DEBUG: cartao_credito = '" . ($cartaoCredito ?: 'null') . "'");
        error_log("DEBUG: id = " . $usuario->getId());
        error_log("DEBUG: === FIM DOS VALORES ===");

        $resultado = $stmt->execute();
        error_log("DEBUG: Resultado execute = " . ($resultado ? 'true' : 'false'));
        
        if (!$resultado) {
            error_log("DEBUG: Erro na execução = " . print_r($stmt->errorInfo(), true));
        }
        
        // Verifica o que foi realmente salvo
        $sqlCheck = "SELECT tipo FROM " . $this->table_name . " WHERE id = :id";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);
        $stmtCheck->execute();
        $rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        error_log("DEBUG: Tipo verificado no banco após UPDATE = '" . $rowCheck['tipo'] . "' (tipo: " . gettype($rowCheck['tipo']) . ")");
        
        return $resultado;
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
        $stmt->bindValue(':tipo', $novoTipo, PDO::PARAM_BOOL);
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
        
        // Log detalhado do valor bruto do tipo
        error_log("DEBUG: Valor bruto do tipo do banco = '" . $row['tipo'] . "' (tipo: " . gettype($row['tipo']) . ")");
        error_log("DEBUG: var_export do tipo = " . var_export($row['tipo'], true));
        error_log("DEBUG: json_encode do tipo = " . json_encode($row['tipo']));
        
        // Converte corretamente o tipo do banco
        $tipoConvertido = false;
        
        // PostgreSQL retorna boolean como 't' ou 'f' quando convertido para string
        if (is_string($row['tipo'])) {
            $tipoConvertido = ($row['tipo'] === '1' || $row['tipo'] === 'true' || $row['tipo'] === 't' || $row['tipo'] === 'TRUE');
            error_log("DEBUG: Tipo é string, convertido para: " . ($tipoConvertido ? 'true' : 'false'));
        } elseif (is_numeric($row['tipo'])) {
            $tipoConvertido = ($row['tipo'] == 1);
            error_log("DEBUG: Tipo é numérico, convertido para: " . ($tipoConvertido ? 'true' : 'false'));
        } elseif (is_bool($row['tipo'])) {
            $tipoConvertido = $row['tipo'];
            error_log("DEBUG: Tipo é boolean, mantido como: " . ($tipoConvertido ? 'true' : 'false'));
        } elseif ($row['tipo'] === null) {
            $tipoConvertido = false;
            error_log("DEBUG: Tipo é null, definido como false");
        } else {
            // Para qualquer outro caso, converte para boolean
            $tipoConvertido = (bool)$row['tipo'];
            error_log("DEBUG: Tipo não reconhecido, convertido para: " . ($tipoConvertido ? 'true' : 'false'));
        }
        
        $usuario->setTipo($tipoConvertido);
        error_log("DEBUG: Tipo final definido no usuário: " . ($usuario->getTipo() ? 'true' : 'false'));
        
        // Converte is_admin da mesma forma
        $isAdminConvertido = false;
        if (is_string($row['is_admin'])) {
            $isAdminConvertido = ($row['is_admin'] === '1' || $row['is_admin'] === 'true' || $row['is_admin'] === 't' || $row['is_admin'] === 'TRUE');
        } elseif (is_numeric($row['is_admin'])) {
            $isAdminConvertido = ($row['is_admin'] == 1);
        } elseif (is_bool($row['is_admin'])) {
            $isAdminConvertido = $row['is_admin'];
        } else {
            $isAdminConvertido = (bool)$row['is_admin'];
        }
        
        $usuario->setIsAdmin($isAdminConvertido);
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