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
    $produto->setCodigo($_POST['codigo']);

    // Processa a foto apenas se uma nova foi enviada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tipo_permitido = ['image/jpeg', 'image/png'];
        if (!in_array($_FILES['foto']['type'], $tipo_permitido)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas JPG ou PNG.");
        }
        
        // Adicionar validação do tamanho (exemplo: 5MB)
        if ($_FILES['foto']['size'] > 8 * 1024 * 1024) {
            throw new Exception("A imagem deve ter no máximo 5MB.");
        }
        
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
        $produto->setFoto($foto);
    }

    if ($produtoDao->atualiza($produto)) {
        header("Location: produtos.php?msg=Produto atualizado com sucesso&tipo=success");
        exit;
    } else {
        throw new Exception("Erro ao atualizar produto");
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Editar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        .container {
            margin-top: 100px;
        }
        .input-text {
            -moz-appearance: textfield !important;
            -webkit-appearance: textfield !important;
            appearance: textfield !important;
        }
    </style>
</head>
<body class="bg-light">
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

    $page_title = "Editar Produto";
    include_once '../layout_header.php';
    ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Editar Produto</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <div class="alert alert-<?php echo $_GET['tipo'] ?? 'danger'; ?>">
                                <?php echo htmlspecialchars($_GET['msg']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="fornecedor_id" value="<?= $produto->getFornecedorId() ?>" />
                            
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código do Produto</label>
                                <input type="text" id="codigo" name="codigo" class="form-control" value="<?= htmlspecialchars($produto->getCodigo() ?? '') ?>" placeholder="Código de identificação do produto" />
                            </div>

                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto</label>
                                <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($produto->getNome()) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea name="descricao" id="descricao" class="form-control" rows="3" required><?= htmlspecialchars($produto->getDescricao()) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="preco" class="form-label">Preço</label>
                                <input type="number" id="preco" name="preco" class="form-control input-text" min="0" step="0.01" value="<?= $produto->getPreco() ?>" required />
                            </div>

                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade em Estoque</label>
                                <input type="number" id="quantidade" name="quantidade" class="form-control" min="0" value="<?= htmlspecialchars($produto->getQuantidade()) ?>" required />
                            </div>

                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto do Produto</label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                               
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="produtos.php" class="btn btn-link">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
