<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se foi passado um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /visualiza_produtos.php?msg=ID do produto inválido&tipo=danger");
    exit();
}

$produtoId = $_GET['id'];
$produtoDao = $factory->getProdutoDao();
$produto = $produtoDao->buscaPorId($produtoId);

$fornecedorDao = $factory->getFornecedorDao();
$fornecedor = $fornecedorDao->buscaPorId($produto->getFornecedorId());

if (!$produto) {
    header("Location: /visualiza_produtos.php?msg=Produto não encontrado&tipo=danger");
    exit();
}

$page_title = "Detalhes do Produto";
include_once '../layout_header.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <img src="../assets/imagem-default.jpg"" class="img-fluid rounded produto-imagem" alt="<?= htmlspecialchars($produto->getNome()) ?>">
            </div>
            <div class="col-md-4">
                <?php if ($produto): ?>
                    <h2><?= htmlspecialchars($produto->getNome()) ?></h2>
                    <p class="fs-5 text-muted mb-4"><?= htmlspecialchars($produto->getDescricao()) ?></p>
                    
                    <div class="mb-4">
                        <p><strong>Fornecedor:</strong> <?= htmlspecialchars($fornecedor->getNome()) ?></p>
                        <p><strong>Preço:</strong> R$ <?= $produto->getPreco() == 0 ? '0' : number_format($produto->getPreco(), 2, ',', '.') ?></p>
                        <?php if($produto->getQuantidade() > 0): ?>
                            <p><strong>Quantidade em Estoque:</strong> <?= $produto->getQuantidade() ?></p>
                        <?php else: ?>
                            <p><strong>Indisponível</strong></p>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary btn-lg">Adicionar ao Carrinho</button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Produto não encontrado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
