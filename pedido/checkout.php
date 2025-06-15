<?php
include_once '../fachada.php';
include_once '../comum.php';
include_once '../model/Pedido.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura o fuso horário para o Brasil
date_default_timezone_set('America/Sao_Paulo');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php?msg=Por favor, faça login para finalizar a compra&tipo=warning");
    exit();
}

// Verifica se o carrinho existe e não está vazio
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header("Location: visualizar_carrinho.php?msg=Seu carrinho está vazio&tipo=warning");
    exit();
}

// Verifica se o usuário tem endereços cadastrados
$usuarioDao = $factory->getUsuarioDao();
$usuario = $usuarioDao->buscaPorId($_SESSION['usuario_id']);
$endereco = $usuario->getEndereco();

if (!$endereco) {
    header("Location: ../endereco/insere_endereco.php?msg=Por favor, cadastre um endereço de entrega&tipo=warning&redirect=checkout");
    exit();
}

// Verifica se o endereço foi fornecido
if (!isset($_POST['endereco_id']) || empty($_POST['endereco_id'])) {
    header("Location: visualizar_carrinho.php?msg=Por favor, selecione um endereço de entrega&tipo=warning");
    exit();
}

try {
    // Obtém a conexão
    $conexao = $factory->getConnection();
    
    // Inicia a transação
    $conexao->beginTransaction();
    
    // Cria o pedido
    $pedido = new Pedido();
    $pedido->setUsuarioId($_SESSION['usuario_id']);
    $pedido->setEnderecoId($_POST['endereco_id']);
    $pedido->setDataPedido(date('Y-m-d H:i:s'));
    $pedido->setStatus('PENDENTE');
    $pedido->setTotal(0);
    
    // Salva o pedido
    $pedidoDao = $factory->getPedidoDao();
    $pedidoId = $pedidoDao->salvar($pedido);
    $pedido->setId($pedidoId);
    
    // Adiciona os itens do pedido
    $total = 0;
    foreach ($_SESSION['carrinho'] as $produtoId => $item) {
        // Busca o produto
        $produtoDao = $factory->getProdutoDao();
        $produto = $produtoDao->buscaPorId($produtoId);
        
        if (!$produto) {
            throw new Exception("Produto não encontrado");
        }
        
        $quantidade = $item['quantidade'];
        
        // Verifica o estoque
        if ($produto->getQuantidade() < $quantidade) {
            throw new Exception("Estoque insuficiente para o produto: " . $produto->getNome());
        }
        
        // Adiciona o item ao pedido
        $pedidoDao->adicionarItemPedido($pedido->getId(), $produtoId, $quantidade, $produto->getPreco());
        
        // Atualiza o estoque
        $produto->setQuantidade($produto->getQuantidade() - $quantidade);
        $produtoDao->atualiza($produto);
        
        // Atualiza o total
        $total += $quantidade * $produto->getPreco();
    }
    
    // Atualiza o total do pedido
    $pedido->setTotal($total);
    $pedidoDao->atualizar($pedido);
    
    // Limpa o carrinho
    unset($_SESSION['carrinho']);
    
    // Confirma a transação
    $conexao->commit();
    
    // Redireciona para a página de sucesso
    header("Location: pedido_sucesso.php?id=" . $pedido->getId());
    exit();
    
} catch (Exception $e) {
    // Desfaz a transação em caso de erro
    if (isset($conexao)) {
        $conexao->rollBack();
    }
    
    // Redireciona com mensagem de erro
    header("Location: visualizar_carrinho.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
    exit();
}
?> 