<?php

include_once '../fachada.php';
include_once '../comum.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

function jsonResponse($tipo, $msg) {
    header('Content-Type: application/json');
    echo json_encode([
        'tipo' => $tipo,
        'msg' => $msg
    ]);
    exit();
}

// Verifica se o usuário está logado e é fornecedor
if (!isset($_SESSION["usuario_id"]) || !isset($_SESSION["is_fornecedor"]) || !$_SESSION["is_fornecedor"]) {
    if ($isAjax) {
        jsonResponse("error", "Acesso não autorizado.");
    } else {
        header("Location: /ProgWebII/login/login.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        jsonResponse("error", "Método de requisição inválido.");
    } else {
        header("Location: produtos.php?msg=Método de requisição inválido");
        exit();
    }
}

try {
    if (empty($_POST['nome']) || empty($_POST['descricao']) || empty($_POST['fornecedor_id']) || empty($_POST['preco']) || empty($_POST['quantidade'])) {
        throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
    }

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $fornecedor_id = $_POST['fornecedor_id'];
    $preco = floatval($_POST['preco']);
    $quantidade = intval($_POST['quantidade']);
    $codigo = $_POST['codigo'] ?? null;

    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Adicionar validação do tipo de arquivo
        $tipo_permitido = ['image/jpeg', 'image/png'];
        if (!in_array($_FILES['foto']['type'], $tipo_permitido)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas JPG ou PNG.");
        }
        
        // Adicionar validação do tamanho (exemplo: 5MB)
        if ($_FILES['foto']['size'] > 8 * 1024 * 1024) {
            throw new Exception("A imagem deve ter no máximo 5MB.");
        }
        
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
    }

    $produto = new Produto(
        nome: $nome,
        descricao: $descricao,
        fornecedor_id: $fornecedor_id,
        preco: $preco,
        quantidade: $quantidade,
        foto: $foto,
        codigo: $codigo
    );

    $dao = $factory->getProdutoDao();

    if (!$dao->insere($produto)) {
        throw new Exception("Erro ao inserir o produto no banco de dados.");
    }

    if ($isAjax) {
        jsonResponse("success", "Produto cadastrado com sucesso");
    } else {
        header("Location: produtos.php?msg=Produto cadastrado com sucesso&tipo=success");
        exit();
    }

} catch (Exception $e) {
    error_log("Erro ao cadastrar produto: " . $e->getMessage());

    if ($isAjax) {
        jsonResponse("error", $e->getMessage());
    } else {
        header("Location: produtos.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
        exit();
    }
}
