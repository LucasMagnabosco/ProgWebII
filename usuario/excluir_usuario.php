<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION["usuario_id"])) {
    header("Location: /ProgWebII/login/login.php");
    exit();
}

$usuario = $factory->getUsuarioDao()->buscaPorId($_SESSION["usuario_id"]);
if (!$usuario || !$usuario->isAdmin()) {
    header("Location: /ProgWebII/index.php");
    exit();
}

// Verifica se recebeu o ID do usuário
if (!isset($_GET['id'])) {
    header("Location: permissoes.php?error=1&message=" . urlencode("ID do usuário não fornecido"));
    exit();
}

try {
    $userId = $_GET['id'];
    
    // Não permite excluir o próprio usuário
    if ($userId == $_SESSION["usuario_id"]) {
        throw new Exception("Não é possível excluir seu próprio usuário");
    }
    
    $usuarioParaExcluir = $factory->getUsuarioDao()->buscaPorId($userId);
    if (!$usuarioParaExcluir) {
        throw new Exception("Usuário não encontrado");
    }

    // Não permite excluir o último admin
    if ($usuarioParaExcluir->isAdmin()) {
        $totalAdmins = 0;
        $todosUsuarios = $factory->getUsuarioDao()->buscaTodos();
        foreach ($todosUsuarios as $u) {
            if ($u->isAdmin()) $totalAdmins++;
        }
        if ($totalAdmins <= 1) {
            throw new Exception("Não é possível excluir o último administrador do sistema");
        }
    }

    // Exclui o usuário
    if ($factory->getUsuarioDao()->removePorId($userId)) {
        header("Location: permissoes.php?success=1&message=" . urlencode("Usuário excluído com sucesso"));
    } else {
        throw new Exception("Erro ao excluir usuário");
    }

} catch (Exception $e) {
    header("Location: permissoes.php?error=1&message=" . urlencode($e->getMessage()));
}
exit(); 