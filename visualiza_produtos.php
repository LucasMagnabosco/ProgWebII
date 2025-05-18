<?php
include_once 'fachada.php';
include_once 'comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: /ProgWebII/login/login.php");
    exit();
}

$page_title = "Produtos";


echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';

echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
include_once 'layout_header.php';

// Busca os produtos formatados em JSON
$produtoDao = $factory->getProdutoDao();
$produtosJson = $produtoDao->buscaTodosFormatados();

// Busca os fornecedores
$fornecedorDao = $factory->getFornecedorDao();
$fornecedores = $fornecedorDao->buscaTodos();

$fornecedoresArr = array_map(function ($f) {
    return [
        'id' => $f->getId(),
        'nome' => $f->getNome()
    ];
}, $fornecedores);
?>

<div class="container mt-4">
    <!-- Barra de Pesquisa, Filtros e Botões -->
    <div class="row mb-4 align-items-center">
        <!-- Filtros -->
        <div class="col-md-3">
            <div class="filtro-section">
                <h5>Filtros</h5>
                <div class="mb-3">
                    <label class="form-label">Fornecedores</label>
                    <select class="form-select" id="filtroFornecedor" onchange="filtrarProdutos()">
                        <option value="">Todos</option>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <option value="<?= $fornecedor->getId() ?>"><?= $fornecedor->getNome() ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Barra de Pesquisa e Botões -->
        <div class="col-md-9">
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" id="pesquisa" class="form-control" placeholder="Buscar produtos..."
                            onkeyup="filtrarProdutos()">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <div class="row">
        <div class="col-12">
            <div id="loading" class="text-center" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-3 g-4" id="listaProdutos">
                <!-- Produtos serão carregados aqui via JavaScript -->
            </div>
        </div>
    </div>
</div>

<style>
    .produto-card {
        transition: transform 0.3s;
    }

    .produto-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .produto-imagem {
        height: 200px;
        object-fit: contain;
    }

    .filtro-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .card-body {
        display: flex;
        flex-direction: column;
    }

    .card-text {
        flex-grow: 1;
    }
</style>

<script>
    // Inicializa os produtos com o JSON do PHP
    let produtos = <?= $produtosJson ?>;
    const fornecedores = <?= json_encode($fornecedoresArr) ?>;

    function exibirProdutos(produtosParaExibir) {
        const listaProdutos = document.getElementById('listaProdutos');
        
        // Log para debug
        console.log('Produtos:', produtosParaExibir.map(p => ({
            id: p.id,
            nome: p.nome,
            tem_foto: p.tem_foto,
            url: p.tem_foto ? `produto/recupera_imagem.php?id=${p.id}&t=${Date.now()}` : './assets/imagem-default.jpg'
        })));

        listaProdutos.innerHTML = produtosParaExibir.map(produto => {
            const imageUrl = produto.tem_foto ? `produto/recupera_imagem.php?id=${produto.id}&t=${Date.now()}` : './assets/imagem-default.jpg';
            console.log(`Gerando URL para ${produto.nome}:`, imageUrl);
            
            return `
            <div class="col">
                <div class="card produto-card h-100">
                    <img 
                    src="${imageUrl}" 
                    class="produto-imagem" 
                    alt="${produto.nome}"
                    onerror="console.error('Erro ao carregar imagem:', this.src); this.src='./assets/imagem-default.jpg'"
                    onload="console.log('Imagem carregada com sucesso:', this.src)"
                    >
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${produto.nome}</h5>
                        <p class="card-text">${produto.descricao}</p>
                        <p class="card-text">
                            <small class="text-muted">
                                Código: ${produto.codigo}<br>
                                Fornecedor: ${(() => {
                                    const fornecedor = fornecedores.find(f => f.id == produto.fornecedor_id);
                                    return fornecedor ? fornecedor.nome : '';
                                })()}
                            </small>
                        </p>
                        <a href="./produto/detalhes_produto.php?id=${produto.id}" class="btn btn-primary w-100 mt-2">Ver Detalhes</a>
                    </div>
                </div>
            </div>`;
        }).join('');
    }
    document.addEventListener('DOMContentLoaded', () => exibirProdutos(produtos));

    function removerAcentos(texto) {
        return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function filtrarProdutos() {
        const pesquisa = $("#pesquisa").val();
        const fornecedor = $("#filtroFornecedor").val();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'produto/busca_ajax.php',
            data: { pesquisa: pesquisa, fornecedor: fornecedor },
            success: function (data) {
                exibirProdutos(data);
            }
        });
    }
    $('#pesquisa').on('keyup', function () {
        filtrarProdutos();
    });
    $('#filtroFornecedor').on('change', function () {
        filtrarProdutos();
    });

</script>