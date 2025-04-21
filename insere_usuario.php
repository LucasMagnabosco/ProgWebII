<?php
include_once 'fachada.php';

$login = @$_POST["email"];
$senha = @$_POST["senha"];
$nome = @$_POST["nome"];
$telefone = @$_POST["telefone"];
$tipo = @$_POST["tipo"];

$usuario = new Usuario(nome,$email,$senha,$telefone, $tipo);
$dao = $factory->getUsuarioDao();
$dao->insere($usuario);

//header("Location: usuarios.php");
exit;

?>