<?php
require_once __DIR__ . '/../fachada.php';
require_once 'PedidoDao.php';
require_once __DIR__ . '/../model/Pedido.php';

class PostgresPedidoDao implements PedidoDao {
    private $conexao;

    public function __construct(PDO $conn)
    {
        $this->conexao = $conn;
    }
    
    private $table_name = 'pedido';
    public function salvar($pedido) {
        try {
            $sql = "INSERT INTO pedido" .
                   " (usuario_id, endereco_id, data_pedido, status, total) VALUES" .
                   " (:usuario_id, :endereco_id, :data_pedido, :status, :total) RETURNING id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":usuario_id", $pedido->getUsuarioId());
            $stmt->bindValue(":endereco_id", $pedido->getEnderecoId());
            $stmt->bindValue(":data_pedido", $pedido->getDataPedido());
            $stmt->bindValue(":status", $pedido->getStatus());
            $stmt->bindValue(":total", $pedido->getTotal());
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row['id'];
        } catch(PDOException $e) {
            throw new Exception("Erro ao salvar pedido: " . $e->getMessage());
        }
    }
    
    public function atualizar($pedido) {
        try {
            $sql = "UPDATE pedido SET" .
                   " usuario_id = :usuario_id," .
                   " endereco_id = :endereco_id," .
                   " data_pedido = :data_pedido," .
                   " status = :status," .
                   " total = :total" .
                   " WHERE id = :id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":usuario_id", $pedido->getUsuarioId());
            $stmt->bindValue(":endereco_id", $pedido->getEnderecoId());
            $stmt->bindValue(":data_pedido", $pedido->getDataPedido());
            $stmt->bindValue(":status", $pedido->getStatus());
            $stmt->bindValue(":total", $pedido->getTotal());
            $stmt->bindValue(":id", $pedido->getId());
            
            return $stmt->execute();
        } catch(PDOException $e) {
            throw new Exception("Erro ao atualizar pedido: " . $e->getMessage());
        }
    }
    
    public function excluir($id) {
        try {
            // Primeiro remove os itens do pedido
            $sql = "DELETE FROM itens_pedido WHERE pedido_id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            // Depois remove o pedido
            $sql = "DELETE FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao excluir pedido: " . $e->getMessage());
        }
    }
    
    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindValue(":id", $id);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return new Pedido(
                    $row['id'],
                    $row['usuario_id'],
                    $row['endereco_id'],
                    $row['data_pedido'],
                    $row['status'],
                    $row['total']
                );
            }
            
            return null;
        } catch(PDOException $e) {
            throw new Exception("Erro ao buscar pedido: " . $e->getMessage());
        }
    }
    
    public function buscarTodos() {
        try {
            $sql = "SELECT * FROM pedido ORDER BY data_pedido DESC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();
            
            $pedidos = [];
            while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedido = new Pedido();
                $pedido->setId($resultado['id']);
                $pedido->setUsuarioId($resultado['usuario_id']);
                $pedido->setEnderecoId($resultado['endereco_id']);
                $pedido->setDataPedido($resultado['data_pedido']);
                $pedido->setStatus($resultado['status']);
                $pedido->setTotal($resultado['total']);
                $pedidos[] = $pedido;
            }
            return $pedidos;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar pedidos: " . $e->getMessage());
        }
    }
    
    public function buscarPorCliente($clienteId) {
        try {
            $sql = "SELECT * FROM pedido WHERE usuario_id = :usuario_id ORDER BY data_pedido DESC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":usuario_id", $clienteId);
            $stmt->execute();
            
            $pedidos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedido = new Pedido(
                    $row['id'],
                    $row['usuario_id'],
                    $row['endereco_id'],
                    $row['data_pedido'],
                    $row['status'],
                    $row['total']
                );
                $pedidos[] = $pedido;
            }
            
            return $pedidos;
        } catch(PDOException $e) {
            throw new Exception("Erro ao buscar pedidos do usuário: " . $e->getMessage());
        }
    }
    
    public function buscarPorStatus($status) {
        try {
            $sql = "SELECT * FROM pedido WHERE status = :status ORDER BY data_pedido DESC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->execute();
            
            $pedidos = [];
            while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedido = new Pedido();
                $pedido->setId($resultado['id']);
                $pedido->setUsuarioId($resultado['usuario_id']);
                $pedido->setEnderecoId($resultado['endereco_id']);
                $pedido->setDataPedido($resultado['data_pedido']);
                $pedido->setStatus($resultado['status']);
                $pedido->setTotal($resultado['total']);
                $pedidos[] = $pedido;
            }
            return $pedidos;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar pedidos por status: " . $e->getMessage());
        }
    }
    
    public function atualizarStatus($pedidoId, $status) {
        try {
            $sql = "UPDATE {$this->table_name} SET status = :status WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $pedidoId);
            $stmt->bindValue(':status', $status);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar status do pedido: " . $e->getMessage());
        }
    }
    
    public function buscarItensPedido($pedidoId, $fornecedorId = null) {
        global $factory;
        try {
            $sql = "SELECT ip.*, p.nome as produto_nome, p.foto as produto_imagem, p.fornecedor_id 
                    FROM itens_pedido ip 
                    JOIN produto p ON ip.produto_id = p.id 
                    WHERE ip.pedido_id = :pedido_id";
            if ($fornecedorId) {
                $sql .= " AND p.fornecedor_id = :fornecedor_id";
            }
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            if ($fornecedorId) {
                $stmt->bindValue(':fornecedor_id', $fornecedorId);
            }
            $stmt->execute();
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($itens as &$item) {
                // Não enviar mais produto_imagem nem produto_imagem_tipo
                // O frontend deve buscar a imagem via get_imagem.php?id=produto_id
            }
            unset($item);
            return $itens;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar itens do pedido: " . $e->getMessage());
        }
    }
    
    public function adicionarItemPedido($pedidoId, $produtoId, $quantidade, $precoUnitario, $pedidoFornecedorId = null) {
        try {
            if ($pedidoFornecedorId === null) {
                // Compatibilidade retroativa
                $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) 
                        VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario)";
            } else {
                $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario, pedido_fornecedor_id) 
                        VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario, :pedido_fornecedor_id)";
            }
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            $stmt->bindValue(':produto_id', $produtoId);
            $stmt->bindValue(':quantidade', $quantidade);
            $stmt->bindValue(':preco_unitario', $precoUnitario);
            if ($pedidoFornecedorId !== null) {
                $stmt->bindValue(':pedido_fornecedor_id', $pedidoFornecedorId);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao adicionar item ao pedido: " . $e->getMessage());
        }
    }
    
    public function removerItemPedido($pedidoId, $produtoId) {
        try {
            $sql = "DELETE FROM itens_pedido 
                    WHERE pedido_id = :pedido_id AND produto_id = :produto_id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            $stmt->bindValue(':produto_id', $produtoId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao remover item do pedido: " . $e->getMessage());
        }
    }
    
    public function atualizarQuantidadeItem($pedidoId, $produtoId, $quantidade) {
        try {
            $sql = "UPDATE itens_pedido 
                    SET quantidade = :quantidade 
                    WHERE pedido_id = :pedido_id AND produto_id = :produto_id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            $stmt->bindValue(':produto_id', $produtoId);
            $stmt->bindValue(':quantidade', $quantidade);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar quantidade do item: " . $e->getMessage());
        }
    }
    
    public function buscarPorCodigoOuNome($termo, $inicio = 0, $quantos = 10, $fornecedorId = null) {
        global $factory;
        if ($fornecedorId) {
            $sql = "SELECT DISTINCT p.* FROM pedido p 
                    JOIN itens_pedido ip ON ip.pedido_id = p.id
                    JOIN produto pr ON ip.produto_id = pr.id
                    JOIN usuario u ON p.usuario_id = u.id
                    WHERE pr.fornecedor_id = :fornecedor_id";
            $params = [':fornecedor_id' => $fornecedorId];
            if (!empty($termo)) {
                $sql .= " AND (UPPER(u.nome) LIKE :termo OR CAST(p.id AS TEXT) LIKE :termo)";
                $params[':termo'] = '%' . strtoupper($termo) . '%';
            }
            $sql .= " ORDER BY p.data_pedido DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conexao->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$quantos, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$inicio, PDO::PARAM_INT);
            $stmt->execute();
            $pedidos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedidos[] = new Pedido(
                    $row['id'],
                    $row['usuario_id'],
                    $row['endereco_id'],
                    $row['data_pedido'],
                    $row['status'],
                    $row['total']
                );
            }
            return $pedidos;
        } else {
            $sql = "SELECT p.* FROM pedido p JOIN usuario u ON p.usuario_id = u.id WHERE 1=1";
            $params = [];
            if (!empty($termo)) {
                $sql .= " AND (UPPER(u.nome) LIKE :termo OR CAST(p.id AS TEXT) LIKE :termo)";
                $params[':termo'] = '%' . strtoupper($termo) . '%';
            }
            $sql .= " ORDER BY p.data_pedido DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conexao->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$quantos, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$inicio, PDO::PARAM_INT);
            $stmt->execute();
            $pedidos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedidos[] = new Pedido(
                    $row['id'],
                    $row['usuario_id'],
                    $row['endereco_id'],
                    $row['data_pedido'],
                    $row['status'],
                    $row['total']
                );
            }
            return $pedidos;
        }
    }
    
    public function buscaTodosFormatados($inicio, $quantos, $termo = '', $fornecedorId = null) {
        global $factory;
        $pedidos = $this->buscarPorCodigoOuNome($termo, $inicio, $quantos, $fornecedorId);
        $pedidosJSON = [];
        foreach ($pedidos as $pedido) {
            $pedidoArr = $pedido->toJson();
            // Buscar subpedidos
            $subpedidos = $this->buscarSubpedidos($pedido->getId());
            $pedidoArr['subpedidos'] = [];
            foreach ($subpedidos as $sub) {
                if ($fornecedorId && $sub['fornecedor_id'] != $fornecedorId) continue;
                // Buscar nome do fornecedor
                $fornecedorObj = $factory->getFornecedorDao()->buscaPorId($sub['fornecedor_id']);
                $fornecedorNome = $fornecedorObj ? $fornecedorObj->getNome() : $sub['fornecedor_id'];
                // Buscar itens do subpedido
                $stmt = $this->conexao->prepare("SELECT ip.*, p.nome as produto_nome, p.id as produto_id, p.descricao as produto_descricao, p.preco as produto_preco, p.quantidade as produto_quantidade, p.fornecedor_id as produto_fornecedor_id, p.codigo as produto_codigo FROM itens_pedido ip JOIN produto p ON ip.produto_id = p.id WHERE ip.pedido_fornecedor_id = :subpedido_id");
                $stmt->bindValue(':subpedido_id', $sub['id']);
                $stmt->execute();
                $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log('ERRO_PEDIDO: Subpedido ' . $sub['id'] . ' tem ' . count($itens) . ' itens.');
                foreach ($itens as &$item) {
                    // Não processe mais nada relacionado à imagem
                }
                $pedidoArr['subpedidos'][] = [
                    'id' => $sub['id'],
                    'fornecedor_id' => $sub['fornecedor_id'],
                    'fornecedor_nome' => $fornecedorNome,
                    'status' => $sub['status'],
                    'total' => $sub['total'],
                    'data_subpedido' => $sub['data_subpedido'],
                    'data_envio' => $sub['data_envio'],
                    'data_cancelamento' => $sub['data_cancelamento'],
                    'itens' => $itens
                ];
                if ($fornecedorId) break;
            }
            // Só aplica o filtro se for fornecedor
            if ($fornecedorId) {
                if (count($pedidoArr['subpedidos']) === 0) continue;
            }
            $pedidosJSON[] = $pedidoArr;
        }
        return json_encode($pedidosJSON, JSON_PRETTY_PRINT);
    }
    
    public function contarPedidos($termo = '', $cliente = null, $fornecedorId = null) {
        if ($fornecedorId) {
            $sql = "SELECT COUNT(DISTINCT p.id) as total FROM pedido p 
                    JOIN itens_pedido ip ON ip.pedido_id = p.id
                    JOIN produto pr ON ip.produto_id = pr.id
                    JOIN usuario u ON p.usuario_id = u.id
                    WHERE pr.fornecedor_id = :fornecedor_id";
            $params = [':fornecedor_id' => $fornecedorId];
            if ($termo) {
                $sql .= " AND (UPPER(u.nome) LIKE :termo OR CAST(p.id AS TEXT) LIKE :termo)";
                $params[':termo'] = '%' . strtoupper($termo) . '%';
            }
            if ($cliente) {
                $sql .= " AND p.usuario_id = :cliente";
                $params[':cliente'] = $cliente;
            }
            $stmt = $this->conexao->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        } else {
            $sql = "SELECT COUNT(*) as total FROM pedido p JOIN usuario u ON p.usuario_id = u.id WHERE 1=1";
            $params = [];
            if ($termo) {
                $sql .= " AND (UPPER(u.nome) LIKE :termo OR CAST(p.id AS TEXT) LIKE :termo)";
                $params[':termo'] = '%' . strtoupper($termo) . '%';
            }
            if ($cliente) {
                $sql .= " AND p.usuario_id = :cliente";
                $params[':cliente'] = $cliente;
            }
            $stmt = $this->conexao->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        }
    }

    // Criar subpedido (pedido_fornecedor) ---
    public function criarSubpedido($pedidoId, $fornecedorId, $status = 'PENDENTE', $total = 0) {
        try {
            $sql = "INSERT INTO pedido_fornecedor (pedido_id, fornecedor_id, status, total, data_subpedido)
                    VALUES (:pedido_id, :fornecedor_id, :status, :total, NOW()) RETURNING id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            $stmt->bindValue(':fornecedor_id', $fornecedorId);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':total', $total);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row['id'];
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar subpedido: " . $e->getMessage());
        }
    }

    //  Buscar subpedidos de um pedido
    public function buscarSubpedidos($pedidoId) {
        try {
            $sql = "SELECT id, pedido_id, fornecedor_id, status, total, data_subpedido, data_envio, data_cancelamento 
                    FROM pedido_fornecedor WHERE pedido_id = :pedido_id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar subpedidos: " . $e->getMessage());
        }
    }

    // Busca pedidos do fornecedor logado, trazendo apenas o subpedido dele em cada pedido
    public function buscarPedidosPorFornecedor($fornecedorId, $inicio = 0, $quantos = 10, $termo = '') {
        global $factory;
        $sql = "SELECT pf.id, pf.pedido_id, pf.fornecedor_id, pf.status, pf.total, pf.data_subpedido, pf.data_envio, pf.data_cancelamento, 
                       p.usuario_id, p.endereco_id, p.data_pedido, p.status as status_pedido, p.total as total_pedido, u.nome as nome_usuario
                FROM pedido_fornecedor pf
                JOIN pedido p ON pf.pedido_id = p.id
                JOIN usuario u ON p.usuario_id = u.id
                WHERE pf.fornecedor_id = :fornecedor_id";
        $params = [':fornecedor_id' => $fornecedorId];
        if (!empty($termo)) {
            $sql .= " AND (UPPER(u.nome) LIKE :termo OR CAST(p.id AS TEXT) LIKE :termo)";
            $params[':termo'] = '%' . strtoupper($termo) . '%';
        }
        $sql .= " ORDER BY p.data_pedido DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conexao->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$quantos, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$inicio, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pedidos = [];
        foreach ($result as $row) {
            // Busca itens do subpedido
            $stmtItens = $this->conexao->prepare("SELECT ip.*, p.nome as produto_nome, p.id as produto_id FROM itens_pedido ip JOIN produto p ON ip.produto_id = p.id WHERE ip.pedido_fornecedor_id = :subpedido_id");
            $stmtItens->bindValue(':subpedido_id', $row['id']);
            $stmtItens->execute();
            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
            // Garantir que produto_imagem seja sempre string ou null
            foreach ($itens as &$item) {
                // Não enviar mais produto_imagem nem produto_imagem_tipo
                // O frontend deve buscar a imagem via get_imagem.php?id=produto_id
            }
            unset($item);
            // Só adiciona se houver itens
            if (count($itens) === 0) continue;
            // Monta o pedido principal
            $pedidoArr = [
                'id' => $row['pedido_id'],
                'usuarioId' => $row['usuario_id'],
                'enderecoId' => $row['endereco_id'],
                'dataPedido' => $row['data_pedido'],
                'status' => $row['status_pedido'],
                'total' => $row['total_pedido'],
                'nomeUsuario' => $row['nome_usuario'],
                'subpedidos' => [] 
            ];
            $fornecedorObj = $factory->getFornecedorDao()->buscaPorId($row['fornecedor_id']);
            $fornecedorNome = $fornecedorObj ? $fornecedorObj->getNome() : $row['fornecedor_id'];
            $pedidoArr['subpedidos'] = [[
                'id' => $row['id'],
                'fornecedor_id' => $row['fornecedor_id'],
                'fornecedor_nome' => $fornecedorNome,
                'status' => $row['status'],
                'total' => $row['total'],
                'data_subpedido' => $row['data_subpedido'],
                'data_envio' => $row['data_envio'],
                'data_cancelamento' => $row['data_cancelamento'],
                'itens' => $itens
            ]];
            $pedidos[] = $pedidoArr;
        }
        return json_encode($pedidos, JSON_PRETTY_PRINT);
    }

    // Atualiza o status de um subpedido (pedido_fornecedor)
    public function atualizarStatusSubpedido($subpedidoId, $novoStatus) {
        try {
            $sql = "UPDATE pedido_fornecedor SET status = :status";
            
            // Adiciona timestamp para status ENVIADO ou CANCELADO
            if ($novoStatus === 'ENVIADO') {
                $sql .= ", data_envio = NOW()";
            } elseif ($novoStatus === 'CANCELADO') {
                $sql .= ", data_cancelamento = NOW()";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':status', $novoStatus);
            $stmt->bindValue(':id', $subpedidoId);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar status do subpedido: " . $e->getMessage());
        }
    }

    // Buscar subpedido por id
    public function buscarSubpedidoPorId($subpedidoId) {
        try {
            $sql = "SELECT id, pedido_id, fornecedor_id, status, total, data_subpedido, data_envio, data_cancelamento 
                    FROM pedido_fornecedor WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $subpedidoId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar subpedido: " . $e->getMessage());
        }
    }

    public function detalharPedido($id, $fornecedorId = null) {
        global $factory;
        $pedido = $this->buscarPorId($id);
        if (!$pedido) return null;
        $usuarioDao = $factory->getUsuarioDao();
        $usuario = $usuarioDao->buscaPorId($pedido->getUsuarioId());
        $pedidoArr = $pedido->toJson();
        $pedidoArr['nomeUsuario'] = $usuario ? $usuario->getNome() : '';
        // Buscar subpedidos
        $subpedidos = $this->buscarSubpedidos($pedido->getId());
        $pedidoArr['subpedidos'] = [];
        foreach ($subpedidos as $sub) {
            if ($fornecedorId && $sub['fornecedor_id'] != $fornecedorId) continue;
            // Buscar nome do fornecedor
            $fornecedorObj = $factory->getFornecedorDao()->buscaPorId($sub['fornecedor_id']);
            $fornecedorNome = $fornecedorObj ? $fornecedorObj->getNome() : $sub['fornecedor_id'];
            // Buscar itens do subpedido
            $stmt = $factory->getConnection()->prepare("SELECT ip.*, p.nome as produto_nome, p.descricao as produto_descricao, p.preco as produto_preco, p.quantidade as produto_quantidade, p.fornecedor_id as produto_fornecedor_id, p.codigo as produto_codigo, p.id as produto_id FROM itens_pedido ip JOIN produto p ON ip.produto_id = p.id WHERE ip.pedido_fornecedor_id = :subpedido_id");
            $stmt->bindValue(':subpedido_id', $sub['id']);
            $stmt->execute();
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($itens as &$item) {
                // Não enviar mais produto_imagem nem produto_imagem_tipo
                // O frontend deve buscar a imagem via get_imagem.php?id=produto_id
            }
            $pedidoArr['subpedidos'][] = [
                'id' => $sub['id'],
                'fornecedor_id' => $sub['fornecedor_id'],
                'fornecedor_nome' => $fornecedorNome,
                'status' => $sub['status'],
                'total' => $sub['total'],
                'data_subpedido' => $sub['data_subpedido'],
                'data_envio' => $sub['data_envio'],
                'data_cancelamento' => $sub['data_cancelamento'],
                'itens' => $itens
            ];
        }
        return json_encode($pedidoArr, JSON_PRETTY_PRINT);
    }
}
?>
