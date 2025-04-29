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
    
    // Cria o fornecedor (que é um tipo de usuário)
    $senhaHash = md5($senha);
    $fornecedor = new Fornecedor($nome, $email, $senhaHash, $telefone, $cnpj, $descricao);
    
    if ($usuarioDao->insere($fornecedor)) {
        $usuarioCriado = $usuarioDao->buscaPorEmail($email);
        if ($usuarioCriado) {
            $_SESSION['usuario_id'] = $usuarioCriado->getId();
            $_SESSION['usuario_nome'] = $usuarioCriado->getNome();
            header("Location: ../endereco/novo_endereco.php");
        } else {
            header("Location: novo_fornecedor.php?msg=Erro ao cadastrar fornecedor&tipo=danger");
        }
    } else {
        header("Location: novo_fornecedor.php?msg=Erro ao cadastrar fornecedor&tipo=danger");
    }
} catch(Exception $e) {
    header("Location: novo_fornecedor.php?msg=" . $e->getMessage() . "&tipo=danger");
}
?> 