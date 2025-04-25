<?php
include_once 'fachada.php';

// Recebe os dados do formulário
$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$telefone = $_POST['telefone'];
$tipo = $_POST['tipo'];

try {
    $dao = $factory->getUsuarioDao();
    
    // Verifica se o email já existe
    $usuarioExistente = $dao->buscaPorEmail($email);
    if ($usuarioExistente) {
        header("Location: novo_usuario.php?msg=Email já cadastrado no sistema");
        exit;
    }
    
    // Se o email não existe, prossegue com a inserção
    $senhaHash = md5($senha);
    $usuario = new Usuario($nome, $email, $senhaHash, $telefone, $tipo);
    
    if ($dao->insere($usuario)) {
        header("Location: login.php?msg=Usuário cadastrado com sucesso&tipo=success");
        // header("Location: login.php?msg=Usuário cadastrado com sucesso");
    } else {
        header("Location: novo_usuario.php?msg=Email já cadastrado no sistema&tipo=danger");
        // header("Location: novo_usuario.php?msg=Erro ao cadastrar usuário");
    }
} catch(Exception $e) {
    header("Location: novo_usuario.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
    // header("Location: novo_usuario.php?msg=" . $e->getMessage());
}
?>