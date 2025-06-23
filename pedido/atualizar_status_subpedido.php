<?php
include_once '../fachada.php';
include_once '../comum.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit();
}

$usuarioId = $_SESSION['usuario_id'];
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$isFornecedor = isset($_SESSION['is_fornecedor']) && $_SESSION['is_fornecedor'];

// Recebe dados via POST
$input = json_decode(file_get_contents('php://input'), true);
$subpedidoId = $input['subpedido_id'] ?? null;
$novoStatus = $input['status'] ?? null;

$statusValidos = ['PENDENTE', 'APROVADO', 'EM_PREPARACAO', 'ENVIADO', 'ENTREGUE', 'CANCELADO'];
if (!$subpedidoId || !$novoStatus || !in_array($novoStatus, $statusValidos)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Parâmetros inválidos']);
    exit();
}

try {
    $pedidoDao = $factory->getPedidoDao();
    // Buscar subpedido
    $subpedido = $pedidoDao->buscarSubpedidoPorId($subpedidoId);
    if (!$subpedido) {
        throw new Exception('Subpedido não encontrado');
    }
    // Verifica permissão: admin ou fornecedor dono
    if (!$isAdmin) {
        if (!$isFornecedor || $subpedido['fornecedor_id'] != ($_SESSION['fornecedor_id'] ?? null)) {
            http_response_code(403);
            echo json_encode(['erro' => 'Sem permissão para alterar este subpedido']);
            exit();
        }
    }
    // Atualiza status
    $pedidoDao->atualizarStatusSubpedido($subpedidoId, $novoStatus);
    echo json_encode(['sucesso' => true, 'status' => $novoStatus]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
} 