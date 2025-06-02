<?php
session_start();

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    // Se estiver logado, redireciona para a página principal do sistema
    //header("Location: .php");
} else {
    // Se não estiver logado, redireciona para a página de login
    header("Location: login/login.php");
}
exit;
?> 