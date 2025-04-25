<?php
include_once '../../fachada.php';

// Verifica se foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: produtos.php?msg=Método de requisição inválido");
    exit;
}

$nome = $_POST['nome'];
$descricao = $_POST['descricao'];
$fornecedor_id = $_POST['fornecedor_id'];
$foto = $_FILES['foto'] ?? null;

try {
    // Verificação e tratamento da imagem
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fotoNome = uniqid('img_') . '_' . basename($foto['name']);
        $fotoPath = $uploadDir . $fotoNome;

        if (!move_uploaded_file($foto['tmp_name'], $fotoPath)) {
            throw new Exception("Erro ao salvar a imagem.");
        }
    } else {
        // Se não houver foto, podemos atribuir um valor nulo ou uma imagem padrão
        $fotoPath = null;  // Ou atribua um valor de imagem padrão
    }

    // Criação do objeto Produto
    $produto = new Produto($nome, $descricao, $fotoPath, $fornecedor_id);

    // Inserção no banco de dados
    $dao = $factory->getProdutoDao(); 

    if ($dao->insere($produto)) {
        header("Location: produtos.php?msg=Produto cadastrado com sucesso&tipo=success");
    } else {
        header("Location: insere_produto.php?msg=Erro ao cadastrar produto&tipo=danger");
    }
} catch (Exception $e) {
    header("Location: insere_produto.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
}
