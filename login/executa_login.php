<?php 

require "../fachada.php"; 

session_start();

$email = isset($_POST["email"]) ? trim($_POST["email"]) : FALSE; 
$senha = isset($_POST["senha"]) ? trim($_POST["senha"]) : FALSE; 

if(!$email || !$senha) { 
    header("Location: login.php?msg=Por favor, preencha todos os campos");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?msg=Email ou senha inválidos");
    exit;
}

try {
    $dao = $factory->getUsuarioDao();
    $usuario = $dao->buscaPorEmail($email);

    if($usuario && md5($senha) === $usuario->getSenha()) { 
        // Login bem sucedido
        $_SESSION["usuario_id"] = $usuario->getId();
        $_SESSION["usuario_nome"] = $usuario->getNome();
        $_SESSION["is_fornecedor"] = $usuario->getTipo();
        $_SESSION["is_admin"] = $usuario->isAdmin();
        header("Location: ../visualiza_produtos.php"); 
        exit; 
    } else {
        // Email ou senha incorretos
        header("Location: login.php?msg=Email ou senha inválidos");
        exit;
    }
} catch(Exception $e) {
    // Erro no banco de dados ou outra exceção
    header("Location: login.php?msg=" . $e->getMessage());
    exit;
}
?>
