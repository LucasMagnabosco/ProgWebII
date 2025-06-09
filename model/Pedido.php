<?php
class Pedido {
    private $id;
    private $clienteId;
    private $dataPedido;
    private $status;
    private $total;
    
    public function __construct($id = null, $clienteId = null, $dataPedido = null, $status = null, $total = 0) {
        $this->id = $id;
        $this->clienteId = $clienteId;
        $this->dataPedido = $dataPedido ?: date('Y-m-d H:i:s');
        $this->status = $status ?: 'PENDENTE';
        $this->total = $total;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getClienteId() {
        return $this->clienteId;
    }
    
    public function setClienteId($clienteId) {
        $this->clienteId = $clienteId;
    }
    
    public function getDataPedido() {
        return $this->dataPedido;
    }
    
    public function setDataPedido($dataPedido) {
        $this->dataPedido = $dataPedido;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function setTotal($total) {
        $this->total = $total;
    }

    public function toJson() {
        return [
            'id' => $this->id,
            'clienteId' => $this->clienteId,
            'dataPedido' => $this->dataPedido,
            'status' => $this->status,
            'total' => $this->total
        ];
    }
}
?> 