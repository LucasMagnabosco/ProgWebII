<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

$usuario = $factory->getUsuarioDao()->buscaPorId($_SESSION["usuario_id"]);
if (!$usuario || !$usuario->isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sem permissão para realizar esta ação']);
    exit();
}

// Verifica se recebeu os parâmetros necessários
if (!isset($_POST['user_id']) || !isset($_POST['is_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit();
}

try {
    $userId = $_POST['user_id'];
    $isAdmin = $_POST['is_admin'] === 'true';
    
    $usuario = $factory->getUsuarioDao()->buscaPorId($userId);
    if (!$usuario) {
        throw new Exception('Usuário não encontrado');
    }

    // Não permite remover o último admin
    if (!$isAdmin && $usuario->isAdmin()) {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE is_admin = true";
        $stmt = $factory->getConnection()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalAdmins = $resultado['total'];
        
        if ($totalAdmins <= 1) {
            throw new Exception('Não é possível remover o último administrador do sistema');
        }
    }

    $factory->getUsuarioDao()->atualizarStatusAdmin($usuario, $isAdmin);
    
    $action = $isAdmin ? 'tornado' : 'removido de';
    echo json_encode([
        'success' => true, 
        'message' => "Usuário {$action} administrador com sucesso!"
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 