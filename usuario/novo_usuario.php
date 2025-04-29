<?php
session_start();
include_once '../fachada.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <h3 class="text-center">Cadastro de Usuário</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <div class="alert alert-<?php echo $_GET['tipo']; ?>">
                                <?php echo htmlspecialchars($_GET['msg']); ?></div>
                        <?php endif; ?>
                        
                        <form action="insere_usuario.php" method="post">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" id="nome" name="nome" class="form-control" required />
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required />
                            </div>
                            
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" id="senha" name="senha" class="form-control" required />
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" class="form-control" required />
                            </div>
                            
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select id="tipo" name="tipo" class="form-control" required>
                                    <option value="cliente">Cliente</option>
                                    <option value="fornecedor">Fornecedor</option>
                                </select>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Cadastrar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="../login/login.php" class="btn btn-link">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




