<?php 

require "fachada.php"; 
 

session_start();

// Validação dos campos
$email = isset($_POST["email"]) ? trim($_POST["email"]) : FALSE; 
$senha = isset($_POST["senha"]) ? trim($_POST["senha"]) : FALSE; 

// Verifica se os campos estão vazios
if(!$email || !$senha) { 
    header("Location: login.php?msg=Por favor, preencha todos os campos");
    exit;
}

// Verifica se o email é válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?msg=Email ou senha inválidos");
    exit;
}

try {
    $dao = $factory->getUsuarioDao();
    $usuario = $dao->buscaPorEmail($email);

    if($usuario && md5($senha) === $usuario->getSenha()) { 
        // Login bem sucedido
        $_SESSION["usuario"] = $usuario;
        header("Location: usuarios.php"); 
        exit; 
    } else {
        // Email ou senha incorretos
        header("Location: login.php?msg=Email ou senha inválidos");
        exit;
    }
} catch(Exception $e) {
    // Erro no banco de dados ou outra exceção
    header("Location: login.php?msg=Erro ao processar login. Tente novamente mais tarde");
    exit;
}
?>
