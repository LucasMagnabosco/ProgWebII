<?php
session_start();
include_once '../fachada.php';


if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuario/novo_usuario.php");
    exit;
}


$rua = $_POST['rua'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];
$bairro = $_POST['bairro'];
$cidade = $_POST['cidade'];
$estado = $_POST['estado'];
$cep = $_POST['cep'];


try {
    $dao = $factory->getUsuarioDao();
    $usuario = $dao->buscaPorId($_SESSION['usuario_id']);
    
    if (!$usuario) {
        header("Location: novo_endereco.php?msg=Usuário não encontrado&tipo=danger");
        exit;
    }
    

    $endereco = new Endereco($rua, $numero, $complemento, $bairro, $cidade, $estado, $cep);
    

    if ($dao->atualizarEndereco($usuario, $endereco)) {

        unset($_SESSION['usuario_id']);
        header("Location: ../login/login.php?msg=Usuário cadastrado com sucesso&tipo=success");
    } else {
        header("Location: novo_endereco.php?msg=Erro ao cadastrar endereço&tipo=danger");
    }
} catch(Exception $e) {
    header("Location: novo_endereco.php?msg=" . $e->getMessage() . "&tipo=danger");
}
?> 