<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é um administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login/login.php?msg=Acesso restrito a administradores&tipo=warning");
    exit();
}

// Verifica se os parâmetros necessários foram fornecidos
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: pedidos.php?msg=Parâmetros inválidos&tipo=danger");
    exit();
}

$pedidoId = $_GET['id'];
$novoStatus = $_GET['status'];

// Valida o status
$statusValidos = ['PENDENTE', 'APROVADO', 'EM_PREPARACAO', 'ENVIADO', 'ENTREGUE', 'CANCELADO'];
if (!in_array($novoStatus, $statusValidos)) {
    header("Location: pedidos.php?msg=Status inválido&tipo=danger");
    exit();
}

try {
    // Busca o pedido
    $pedidoDao = $factory->getPedidoDao();
    $pedido = $pedidoDao->buscarPorId($pedidoId);
    
    if (!$pedido) {
        throw new Exception("Pedido não encontrado");
    }
    
    // Atualiza o status
    $pedido->setStatus($novoStatus);
    $pedidoDao->atualizar($pedido);
    
    // Redireciona com mensagem de sucesso
    header("Location: pedidos.php?msg=Status atualizado com sucesso&tipo=success");
    exit();
    
} catch (Exception $e) {
    // Redireciona com mensagem de erro
    header("Location: pedidos.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
    exit();
} 