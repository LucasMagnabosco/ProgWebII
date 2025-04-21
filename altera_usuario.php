<?php
include_once 'fachada.php';

$login = @$_POST["email"];
$senha = @$_POST["senha"];
$nome = @$_POST["nome"];
$telefone = @$_POST["telefone"];
$tipo = @$_POST["tipo"];

$senhaHash = md5($senha);

$usuario = new Usuario($nome, $login, $senhaHash, $telefone, $tipo);

$dao = $factory->getUsuarioDao();
$dao->altera($usuario);


header("Location: usuarios.php");
exit;

?>