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
    header("Location: /ProgWebII/login/login.php?msg=Fornecedor não encontrado&tipo=danger");
    exit();
}

error_log("Fornecedor encontrado - ID do usuário: " . $fornecedor->getId() . 
          ", ID do fornecedor: " . $fornecedor->getFornecedorId());

$page_title = "Meus Produtos";
include_once '../layout_header.php';


$produtoDao = $factory->getProdutoDao();
$produtos = $produtoDao->buscaPorFornecedor($fornecedor->getFornecedorId());

error_log("Produtos encontrados: " . print_r($produtos, true));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Meus Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    .highlight {
        background-color: yellow;
        font-weight: bold;
    }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="position-relative mb-4">
            <h3 class="text-center">Meus Produtos</h3>
            <a href="cadastra_produto.php" class="btn btn-success position-absolute end-0 top-0">Cadastrar Novo Produto</a>
        </div>

        <!-- Campo de Pesquisa -->
        <div class="mb-3">
            <input type="text" id="pesquisa" class="form-control" placeholder="Pesquisar produtos..." onkeyup="filtrarTabela()" />
        </div>

        <table class="table table-striped" id="tabelaProdutos">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= htmlspecialchars($produto['nome']) ?></td>
                        <td><?= htmlspecialchars($produto['descricao']) ?></td>
                        <td>
                            <a href="editar_produto.php?id=<?= $produto['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="excluir_produto.php?id=<?= $produto['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Filtro e destaque -->
    <script>
    function filtrarTabela() {
        var input = document.getElementById("pesquisa");
        var filtro = input.value.toLowerCase();
        var tabela = document.getElementById("tabelaProdutos");
        var trs = tabela.getElementsByTagName("tr");

        for (var i = 1; i < trs.length; i++) { // Começa do 1 para pular o cabeçalho
            var tds = trs[i].getElementsByTagName("td");
            if (tds.length > 0) {
                var nomeOriginal = tds[0].textContent;
                var descricaoOriginal = tds[1].textContent;

                var nome = nomeOriginal.toLowerCase();
                var descricao = descricaoOriginal.toLowerCase();

                if (nome.includes(filtro) || descricao.includes(filtro)) {
                    trs[i].style.display = "";

                    // Destacar texto encontrado
                    tds[0].innerHTML = destacarTexto(nomeOriginal, filtro);
                    tds[1].innerHTML = destacarTexto(descricaoOriginal, filtro);
                } else {
                    trs[i].style.display = "none";
                }
            }
        }
    }

    function destacarTexto(texto, filtro) {
        if (!filtro) return texto; // Se filtro vazio, não destaca
        var regex = new RegExp("(" + filtro + ")", "gi");
        return texto.replace(regex, '<span class="highlight">$1</span>');
    }
    </script>
</body>
</html>
