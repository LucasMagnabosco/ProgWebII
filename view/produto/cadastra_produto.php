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
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Cadastro de Produto</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        include_once '../../fachada.php';

                        if (isset($_GET['msg'])):
                            echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['msg']) . '</div>';
                        endif;

                        // Busca os fornecedores cadastrados para o combobox
                        $fornecedorDao = $factory->getFornecedorDao();
                        $fornecedores = $fornecedorDao->buscaTodos();
                        ?>

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
                                <label for="fornecedor_id" class="form-label">Fornecedor</label>
                                <select id="fornecedor_id" name="fornecedor_id" class="form-select" required>
                                    <option value="">Selecione um fornecedor</option>
                                    <?php foreach ($fornecedores as $fornecedor): ?>
                                        <option value="<?= $fornecedor->getId() ?>">
                                            <?= htmlspecialchars($fornecedor->getNome()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

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
