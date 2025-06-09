<?php
require_once 'PedidoDao.php';
require_once 'Conexao.php';

class PostgresPedidoDao implements PedidoDao {
    private $conexao;

    public function __construct(PDO $conn)
    {
        $this->conexao = $conn;
    }
    
    private $table_name = 'pedido';
    public function salvar($pedido) {
        try {
            $sql = "INSERT INTO {$this->table_name} (cliente_id, data_pedido, status, total) 
                    VALUES (:cliente_id, :data_pedido, :status, :total)";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':cliente_id', $pedido->getClienteId());
            $stmt->bindValue(':data_pedido', $pedido->getDataPedido());
            $stmt->bindValue(':status', $pedido->getStatus());
            $stmt->bindValue(':total', $pedido->getTotal());
            
            $stmt->execute();
            return $this->conexao->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar pedido: " . $e->getMessage());
        }
    }
    
    public function atualizar($pedido) {
        try {
            $sql = "UPDATE {$this->table_name} 
                    SET cliente_id = :cliente_id, 
                        data_pedido = :data_pedido, 
                        status = :status, 
                        total = :total 
                    WHERE id = :id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $pedido->getId());
            $stmt->bindValue(':cliente_id', $pedido->getClienteId());
            $stmt->bindValue(':data_pedido', $pedido->getDataPedido());
            $stmt->bindValue(':status', $pedido->getStatus());
            $stmt->bindValue(':total', $pedido->getTotal());
            
            return $stmt->execute();
        } catch (PDOException $e) {
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
            $sql = "SELECT * FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultado) {
                $pedido = new Pedido();
                $pedido->setId($resultado['id']);
                $pedido->setClienteId($resultado['cliente_id']);
                $pedido->setDataPedido($resultado['data_pedido']);
                $pedido->setStatus($resultado['status']);
                $pedido->setTotal($resultado['total']);
                return $pedido;
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar pedido: " . $e->getMessage());
        }
    }
    
    public function buscarTodos() {
        try {
            $sql = "SELECT * FROM {$this->table_name} ORDER BY data_pedido DESC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();
            
            $pedidos = [];
            while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedido = new Pedido();
                $pedido->setId($resultado['id']);
                $pedido->setClienteId($resultado['cliente_id']);
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
            $sql = "SELECT * FROM {$this->table_name} WHERE cliente_id = :cliente_id ORDER BY data_pedido DESC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':cliente_id', $clienteId);
            $stmt->execute();
            
            $pedidos = [];
            while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedido = new Pedido();
                $pedido->setId($resultado['id']);
                $pedido->setClienteId($resultado['cliente_id']);
                $pedido->setDataPedido($resultado['data_pedido']);
                $pedido->setStatus($resultado['status']);
                $pedido->setTotal($resultado['total']);
                $pedidos[] = $pedido;
            }
            return $pedidos;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar pedidos do cliente: " . $e->getMessage());
        }
    }
    
    public function buscarPorStatus($status) {
        try {
            $sql = "SELECT * FROM {$this->table_name} WHERE status = :status ORDER BY data_pedido DESC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->execute();
            
            $pedidos = [];
            while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pedido = new Pedido();
                $pedido->setId($resultado['id']);
                $pedido->setClienteId($resultado['cliente_id']);
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
    
    public function buscarItensPedido($pedidoId) {
        try {
            $sql = "SELECT ip.*, p.nome as produto_nome 
                    FROM itens_pedido ip 
                    JOIN produtos p ON ip.produto_id = p.id 
                    WHERE ip.pedido_id = :pedido_id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar itens do pedido: " . $e->getMessage());
        }
    }
    
    public function adicionarItemPedido($pedidoId, $produtoId, $quantidade, $precoUnitario) {
        try {
            $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) 
                    VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario)";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pedido_id', $pedidoId);
            $stmt->bindValue(':produto_id', $produtoId);
            $stmt->bindValue(':quantidade', $quantidade);
            $stmt->bindValue(':preco_unitario', $precoUnitario);
            
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
}
?>
