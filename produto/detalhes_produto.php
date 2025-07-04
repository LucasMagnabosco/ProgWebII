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

// Prepara a imagem do produto
$foto = $produto->getFoto();
$fotoBase64 = null;
$fotoTipo = null;

if ($foto) {
    if (is_resource($foto)) {
        $foto = stream_get_contents($foto);
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $fotoTipo = $finfo->buffer($foto);
    $fotoBase64 = base64_encode($foto);
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
        .produto-imagem {
            max-height: 500px;
            object-fit: contain;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <img 
                    src="<?= $fotoBase64 ? "data:{$fotoTipo};base64,{$fotoBase64}" : '../assets/imagem-default.jpg' ?>" 
                    class="img-fluid rounded produto-imagem" 
                    alt="<?= htmlspecialchars($produto->getNome()) ?>"
                    onerror="this.src='../assets/imagem-default.jpg'"
                >
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
                        <form action="../pedido/carrinho.php" method="POST">
                            <input type="hidden" name="action" value="adicionar">
                            <input type="hidden" name="produto_id" value="<?= $produto->getId() ?>">
                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade:</label>
                                <input type="number" name="quantidade" id="quantidade" class="form-control" 
                                       value="1" min="1" max="<?= $produto->getQuantidade() ?>" 
                                       <?= $produto->getQuantidade() <= 0 ? 'disabled' : '' ?>>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg <?= $produto->getQuantidade() <= 0 ? 'opacity-50' : '' ?>" 
                                    <?= $produto->getQuantidade() <= 0 ? 'disabled' : '' ?>>
                                Adicionar ao Carrinho
                            </button>
                        </form>
                        <?php if (isset($_GET['adicionado'])): ?>
                        <div class="mt-3">
                            <a href="../pedido/visualizar_carrinho.php" class="btn btn-success">
                                <i class="fas fa-shopping-cart"></i> Ir ao Carrinho
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Produto não encontrado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['sucesso'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Sucesso!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?= $_SESSION['sucesso'] ?>
                <div class="mt-2 pt-2 border-top">
                    <a href="../pedido/visualizar_carrinho.php" class="btn btn-primary btn-sm">Ir ao Carrinho</a>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['sucesso']); endif; ?>

</body>
</html>
