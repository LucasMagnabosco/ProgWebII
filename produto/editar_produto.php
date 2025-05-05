<!-- view -->
<?php
include_once '../fachada.php';

$produtoDao = $factory->getProdutoDao();
$fornecedorDao = $factory->getFornecedorDao();

// Verifica se recebeu o ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: produtos.php?msg=ID do produto não fornecido&tipo=danger");
    exit;
}

$id = $_GET['id'];
$produto = $produtoDao->buscaPorId($id);
$fornecedores = $fornecedorDao->buscaTodos();

if (!$produto) {
    header("Location: produtos.php?msg=Produto não encontrado&tipo=danger");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto->setId($id);
    $produto->setNome($_POST['nome']);
    $produto->setDescricao($_POST['descricao']);
    $produto->setFornecedorId($_POST['fornecedor_id']);
    $produto->setPreco($_POST['preco']);
    $produto->setQuantidade($_POST['quantidade']);

    // Atualizar imagem, se fornecida
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fotoNome = uniqid('img_') . '_' . basename($_FILES['foto']['name']);
        $fotoPath = $uploadDir . $fotoNome;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPath)) {
            $produto->setFoto($fotoPath);
        }
    }

    if ($produtoDao->atualiza($produto)) {
        header("Location: produtos.php?msg=Produto atualizado com sucesso&tipo=success");
    } else {
        header("Location: produtos.php?msg=Erro ao atualizar produto&tipo=danger");
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header text-center">
                <h4>Editar Produto</h4>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($produto->getNome()) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control" rows="3" required><?= htmlspecialchars($produto->getDescricao()) ?></textarea>
                    </div>

                    <!-- <div class="mb-3">
                        <label for="fornecedor_id" class="form-label">Fornecedor</label>
                        <select name="fornecedor_id" id="fornecedor_id" class="form-select" required>
                            <?php foreach ($fornecedores as $f): ?>
                                <option value="<?= $f->getId() ?>" <?= $f->getId() == $produto->getFornecedorId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f->getNome()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div> -->

                    <div class="mb-3">
                        <label for="preco" class="form-label">Preço</label>
                        <input type="number" id="preco" name="preco" class="form-control" min="0" step="0.01" value="<?= $produto->getPreco() ?>" required />
                    </div>

                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade em Estoque</label>
                        <input type="number" id="quantidade" name="quantidade" class="form-control" min="0" value="<?= htmlspecialchars($produto->getQuantidade()) ?>" required />
                    </div>

                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto (opcional)</label>
                        <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                        <?php if ($produto->getFoto()): ?>
                            <div class="mt-2">
                                <img src="<?= $produto->getFoto() ?>" alt="Foto atual" style="max-width: 150px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="produtos.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
</body>
</html>
