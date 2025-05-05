<!-- view -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cadastro de Produto</title>
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

    // Busca o fornecedor logado
    $fornecedorDao = $factory->getFornecedorDao();
    $fornecedor = $fornecedorDao->buscaPorUsuarioId($_SESSION["usuario_id"]);

    if (!$fornecedor) {
        header("Location: produtos.php?msg=Fornecedor não encontrado&tipo=danger");
        exit();
    }

    $page_title = "Cadastro de Produto";
    include_once '../layout_header.php';
    ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Cadastro de Produto</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <div class="alert alert-<?php echo $_GET['tipo'] ?? 'danger'; ?>">
                                <?php echo htmlspecialchars($_GET['msg']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="insere_produto.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto</label>
                                <input type="text" id="nome" name="nome" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea id="descricao" name="descricao" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="preco" class="form-label">Preço</label>
                                <input type="number" id="preco" name="preco" class="form-control input-text" min="0" step="0.01" required />
                            </div>

                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade em Estoque</label>
                                <input type="number" id="quantidade" name="quantidade" class="form-control" min="0" required />
                            </div>

                            <input type="hidden" name="fornecedor_id" value="<?= $fornecedor->getFornecedorId() ?>" />

                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto do Produto (opcional)</label>
                                <input type="file" id="foto" name="foto" class="form-control" accept="image/*" />
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Cadastrar Produto</button>
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
