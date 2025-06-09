<?php
interface PedidoDao {
    public function salvar($pedido);
    public function atualizar($pedido);
    public function excluir($id);
    public function buscarPorId($id);
    public function buscarTodos();
    public function buscarPorCliente($clienteId);
    public function buscarPorStatus($status);
    public function atualizarStatus($pedidoId, $status);
    public function buscarItensPedido($pedidoId);
    public function adicionarItemPedido($pedidoId, $produtoId, $quantidade, $precoUnitario);
    public function removerItemPedido($pedidoId, $produtoId);
    public function atualizarQuantidadeItem($pedidoId, $produtoId, $quantidade);
}
?> 