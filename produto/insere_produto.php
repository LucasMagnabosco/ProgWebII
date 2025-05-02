<!-- controller -->
<?php

include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é fornecedor
if (!isset($_SESSION["usuario_id"]) || !isset($_SESSION["is_fornecedor"]) || !$_SESSION["is_fornecedor"]) {
    header("Location: /ProgWebII/login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: produtos.php?msg=Método de requisição inválido");
    exit;
}

try {
    // Validação dos campos obrigatórios
    if (empty($_POST['nome']) || empty($_POST['descricao']) || empty($_POST['fornecedor_id'])) {
        throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
    }

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $fornecedor_id = $_POST['fornecedor_id'];
    $foto = $_FILES['foto'] ?? null;

    error_log("Dados recebidos - Nome: $nome, Descrição: $descricao, Fornecedor ID: $fornecedor_id");

    // Verificação e tratamento da imagem
    $fotoPath = null;
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fotoNome = uniqid('img_') . '_' . basename($foto['name']);
        $fotoPath = 'uploads/' . $fotoNome;

        if (!move_uploaded_file($foto['tmp_name'], $uploadDir . $fotoNome)) {
            throw new Exception("Erro ao salvar a imagem.");
        }
    }

    // Criação do objeto Produto
    $produto = new Produto(
        nome: $nome,
        descricao: $descricao,
        fornecedor_id: $fornecedor_id,
        foto: $fotoPath
    );

    error_log("Objeto Produto criado - Nome: " . $produto->getNome() . 
              ", Descrição: " . $produto->getDescricao() . 
              ", Fornecedor ID: " . $produto->getFornecedorId() . 
              ", Foto: " . $produto->getFoto());

    // Inserção no banco de dados
    $dao = $factory->getProdutoDao();
    
    if (!$dao->insere($produto)) {
        throw new Exception("Erro ao inserir o produto no banco de dados.");
    }

    header("Location: produtos.php?msg=Produto cadastrado com sucesso&tipo=success");
    exit();

} catch (Exception $e) {
    error_log("Erro ao cadastrar produto: " . $e->getMessage());
    header("Location: produtos.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
    exit();
}
