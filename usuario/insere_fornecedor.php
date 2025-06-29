<?php
session_start();
include_once '../fachada.php';

$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$telefone = $_POST['telefone'];
$cnpj = $_POST['cnpj'];
$descricao = $_POST['descricao'];

try {
    // Verifica se o email já está cadastrado
    $usuarioDao = $factory->getUsuarioDao();
    $usuarioExistente = $usuarioDao->buscaPorEmail($email);
    if ($usuarioExistente) {
        header("Location: novo_fornecedor.php?msg=Email já cadastrado no sistema&tipo=danger");
        exit;
    }

    // Verifica se o CNPJ já está cadastrado
    $fornecedorDao = $factory->getFornecedorDao();
    $fornecedores = $fornecedorDao->buscaTodos();
    foreach ($fornecedores as $fornecedor) {
        if ($fornecedor->getCnpj() === $cnpj) {
            header("Location: novo_fornecedor.php?msg=CNPJ já cadastrado no sistema&tipo=danger");
            exit;
        }
    }
    
    $senhaHash = md5($senha);
    $fornecedor = new Fornecedor($nome, $email, $senhaHash, $telefone, $cnpj, $descricao);
    
    if ($fornecedorDao->insere($fornecedor)) {
        $usuarioCriado = $usuarioDao->buscaPorEmail($email);
        if ($usuarioCriado) {
            $_SESSION['usuario_id'] = $usuarioCriado->getId();
            $_SESSION['usuario_nome'] = $usuarioCriado->getNome();
            $_SESSION['is_fornecedor'] = $usuarioCriado->getTipo();
            $_SESSION['is_admin'] = $usuarioCriado->isAdmin();
            // Buscar o fornecedor_id e salvar na sessão
            $fornecedorObj = $fornecedorDao->buscaPorUsuarioId($usuarioCriado->getId());
            if ($fornecedorObj) {
                $_SESSION['fornecedor_id'] = method_exists($fornecedorObj, 'getFornecedorId') ? $fornecedorObj->getFornecedorId() : $fornecedorObj->getId();
            }
            header("Location: ../endereco/novo_endereco.php");
        } else {
            header("Location: novo_fornecedor.php?msg=Erro ao cadastrar fornecedor&tipo=danger");
            exit;
        }
    } else {
        header("Location: novo_fornecedor.php?msg=Erro ao cadastrar fornecedor&tipo=danger");
        exit;
    }
} catch(Exception $e) {
    header("Location: novo_fornecedor.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
    exit;
}
?> 