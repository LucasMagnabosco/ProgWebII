<?php
include_once '../fachada.php';
include_once '../comum.php';

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
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

// Recebe o ID do usuário a ser buscado
$userId = $_POST['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
    exit();
}

try {
    $usuarioDao = $factory->getUsuarioDao();
    $usuarioBuscado = $usuarioDao->buscaPorId($userId);
    
    if (!$usuarioBuscado) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit();
    }
    
    // Busca dados do fornecedor se for fornecedor
    $fornecedor = null;
    if ($usuarioBuscado->getTipo()) {
        $fornecedorDao = $factory->getFornecedorDao();
        $fornecedor = $fornecedorDao->buscaPorUsuarioId($userId);
    }
    
    $dadosUsuario = [
        'id' => $usuarioBuscado->getId(),
        'nome' => $usuarioBuscado->getNome(),
        'email' => $usuarioBuscado->getEmail(),
        'telefone' => $usuarioBuscado->getTelefone(),
        'tipo' => $usuarioBuscado->getTipo(),
        'is_admin' => $usuarioBuscado->isAdmin(),
        'cpf_cnpj' => $fornecedor ? $fornecedor->getCnpj() : '',
        'descricao' => $fornecedor ? $fornecedor->getDescricao() : ''
    ];
    
    echo json_encode(['success' => true, 'usuario' => $dadosUsuario]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar usuário: ' . $e->getMessage()]);
}
?> 