<?php
session_start();
include_once '../fachada.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuario/novo_usuario.php");
    exit;
}

// Verifica se os dados do formulário foram enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: novo_endereco.php");
    exit;
}

// Verifica se todos os campos obrigatórios foram preenchidos
$campos_obrigatorios = ['rua', 'numero', 'bairro', 'cidade', 'estado', 'cep'];
$campos_faltando = [];

foreach ($campos_obrigatorios as $campo) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        $campos_faltando[] = $campo;
    }
}

if (!empty($campos_faltando)) {
    header("Location: novo_endereco.php?msg=Por favor, preencha todos os campos obrigatórios&tipo=warning");
    exit;
}

// Obtém os dados do formulário
$rua = $_POST['rua'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'] ?? '';
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
        // Verifica se deve redirecionar para o checkout
        if (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') {
            header("Location: ../pedido/selecionar_endereco.php?msg=Endereço cadastrado com sucesso&tipo=success");
            exit();
        } else {
            header("Location: ../pedido/selecionar_endereco.php?msg=Endereço cadastrado com sucesso&tipo=success");
            exit();
        }
    } else {
        header("Location: novo_endereco.php?msg=Erro ao cadastrar endereço&tipo=danger");
        exit();
    }
} catch(Exception $e) {
    header("Location: novo_endereco.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
}
?> 