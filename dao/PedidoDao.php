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
    public function buscarItensPedido($pedidoId, $fornecedorId = null);
    public function adicionarItemPedido($pedidoId, $produtoId, $quantidade, $precoUnitario, $pedidoFornecedorId = null);
    public function removerItemPedido($pedidoId, $produtoId);
    public function atualizarQuantidadeItem($pedidoId, $produtoId, $quantidade);
    public function buscarPorCodigoOuNome($termo, $inicio = 0, $quantos = 10, $fornecedorId = null);
    public function buscaTodosFormatados($inicio, $quantos, $termo = '', $fornecedorId = null);
    public function contarPedidos($termo = '', $cliente = null, $fornecedorId = null);
    public function criarSubpedido($pedidoId, $fornecedorId, $status = 'PENDENTE', $total = 0);
    public function buscarSubpedidos($pedidoId);
}
?> 