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

    $conexao = $factory->getConnection();

    $conexao->beginTransaction();
    

    $pedido = new Pedido();
    $pedido->setUsuarioId($_SESSION['usuario_id']);
    $pedido->setEnderecoId($_POST['endereco_id']);
    $pedido->setDataPedido(date('Y-m-d H:i:s'));
    $pedido->setStatus('PENDENTE');
    $pedido->setTotal(0);
    

    $pedidoDao = $factory->getPedidoDao();
    $produtoDao = $factory->getProdutoDao();
    $pedidoId = $pedidoDao->salvar($pedido);
    $pedido->setId($pedidoId);

    // 1. Separa itens do carrinho por fornecedor
    $itensPorFornecedor = [];
    foreach ($_SESSION['carrinho'] as $produtoId => $item) {
        $produto = $produtoDao->buscaPorId($produtoId);
        if (!$produto) {
            throw new Exception("Produto não encontrado");
        }
        $fornecedorId = $produto->getFornecedorId();
        if (!isset($itensPorFornecedor[$fornecedorId])) {
            $itensPorFornecedor[$fornecedorId] = [];
        }
        $itensPorFornecedor[$fornecedorId][] = [
            'produto' => $produto,
            'quantidade' => $item['quantidade'],
            'preco' => $produto->getPreco()
        ];
    }

    // 2. Cria subpedidos e insere itens
    $totalPedido = 0;
    foreach ($itensPorFornecedor as $fornecedorId => $itens) {
        // Cria subpedido
        $subpedidoId = $pedidoDao->criarSubpedido($pedidoId, $fornecedorId, 'PENDENTE', 0);
        $totalSubpedido = 0;
        foreach ($itens as $item) {
            $produto = $item['produto'];
            $quantidade = $item['quantidade'];
            $preco = $item['preco'];
            // Verifica estoque
            if ($produto->getQuantidade() < $quantidade) {
                throw new Exception("Estoque insuficiente para o produto: " . $produto->getNome());
            }
            // Insere item
            $pedidoDao->adicionarItemPedido($pedidoId, $produto->getId(), $quantidade, $preco, $subpedidoId);
            // Atualiza estoque
            $produto->setQuantidade($produto->getQuantidade() - $quantidade);
            $produtoDao->atualiza($produto);
            $totalSubpedido += $quantidade * $preco;
        }
        // Atualiza total do subpedido
        $conexao->prepare("UPDATE pedido_fornecedor SET total = :total WHERE id = :id")
            ->execute([':total' => $totalSubpedido, ':id' => $subpedidoId]);
        $totalPedido += $totalSubpedido;
    }

    // Atualiza o total do pedido principal
    $pedido->setTotal($totalPedido);
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