<?php
session_start();
include_once '../fachada.php';

$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$telefone = $_POST['telefone'];
$tipo = $_POST['tipo'];

try {
    $dao = $factory->getUsuarioDao();
    
    $usuarioExistente = $dao->buscaPorEmail($email);
    if ($usuarioExistente) {
        header("Location: novo_usuario.php?msg=Email já cadastrado no sistema&tipo=danger");
        exit;
    }
    
    $senhaHash = md5($senha);
    $usuario = new Usuario($nome, $email, $senhaHash, $telefone, $tipo);
    
    if ($dao->insere($usuario)) {
        $usuarioCriado = $dao->buscaPorEmail($email);
        if ($usuarioCriado) {
            $_SESSION['usuario_id'] = $usuarioCriado->getId();
            $_SESSION['usuario_nome'] = $usuarioCriado->getNome();
            header("Location: ../endereco/novo_endereco.php");
        } else {
            header("Location: novo_usuario.php?msg=Erro ao cadastrar usuário&tipo=danger");
        }
    } else {
        header("Location: novo_usuario.php?msg=Erro ao cadastrar usuário&tipo=danger");
    }
} catch(Exception $e) {
    header("Location: novo_usuario.php?msg=" . $e->getMessage() . "&tipo=danger");
}
?>
