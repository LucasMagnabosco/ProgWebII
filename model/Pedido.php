<?php
class Pedido {
    private $id;
    private $usuarioId;
    private $enderecoId;
    private $dataPedido;
    private $status;
    private $total;
    
    // Constantes para os status do pedido
    const STATUS_PENDENTE = 'PENDENTE';
    const STATUS_APROVADO = 'APROVADO';
    const STATUS_EM_PREPARACAO = 'EM_PREPARACAO';
    const STATUS_ENVIADO = 'ENVIADO';
    const STATUS_ENTREGUE = 'ENTREGUE';
    const STATUS_CANCELADO = 'CANCELADO';
    
    public function __construct($id = null, $usuarioId = null, $enderecoId = null, $dataPedido = null, $status = null, $total = null) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->enderecoId = $enderecoId;
        $this->dataPedido = $dataPedido;
        $this->status = $status ?? self::STATUS_PENDENTE;
        $this->total = $total;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getUsuarioId() {
        return $this->usuarioId;
    }
    
    public function getEnderecoId() {
        return $this->enderecoId;
    }
    
    public function getDataPedido() {
        return $this->dataPedido;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setUsuarioId($usuarioId) {
        $this->usuarioId = $usuarioId;
    }
    
    public function setEnderecoId($enderecoId) {
        $this->enderecoId = $enderecoId;
    }
    
    public function setDataPedido($dataPedido) {
        $this->dataPedido = $dataPedido;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function setTotal($total) {
        $this->total = $total;
    }

    public function toJson() {
        return [
            'id' => $this->id,
            'usuarioId' => $this->usuarioId,
            'enderecoId' => $this->enderecoId,
            'dataPedido' => $this->dataPedido,
            'status' => $this->status,
            'total' => $this->total
        ];
    }
}
?> 