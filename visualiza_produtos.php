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
$inicio = 0; 
$quantos = 6; 
$produtosJson = $produtoDao->buscaTodosFormatados($inicio, $quantos);

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
    <div class="row mb-3">
        <div class="col-md-6 offset-md-6">
            <div class="input-group input-group-sm">
                <input type="text" id="pesquisa" class="form-control form-control-sm" placeholder="Pesquisar produtos...">
                <span class="input-group-text bg-primary text-white">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4" id="listaProdutos">
        <!-- Produtos serão carregados aqui -->
    </div>

    <div id="pagination-container" class="d-flex justify-content-center mt-4">
        <!-- Paginação será carregada aqui -->
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
        
        if (typeof produtosParaExibir === 'string') {
            produtosParaExibir = JSON.parse(produtosParaExibir);
        }
        
        if (produtosParaExibir && produtosParaExibir.length > 0) {
            listaProdutos.innerHTML = produtosParaExibir.map(produto => {
                const imageUrl = produto.foto ? 
                    `data:${produto.foto_tipo};base64,${produto.foto}` : 
                    './assets/imagem-default.jpg';
                
                return `
                <div class="col">
                    <div class="card produto-card h-100">
                        <img 
                            src="${imageUrl}" 
                            class="produto-imagem" 
                            alt="${produto.nome}"
                            onerror="this.src='./assets/imagem-default.jpg'"
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
        } else {
            listaProdutos.innerHTML = '<div class="col-12 text-center">Nenhum produto encontrado</div>';
        }
    }

    function filtrarProdutos(page = 1) {
        const pesquisa = $("#pesquisa").val();
        const fornecedor = $("#filtroFornecedor").val();
        
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'produto/busca_produto_ajax.php',
            data: { 
                pesquisa: pesquisa, 
                page: page
            },
            success: function (data) {
                console.log('Dados recebidos:', data); // Debug
                exibirProdutos(data.produtos);
                if (data.pagination) {
                    $('#pagination-container').html(data.pagination);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição:', error);
                console.log('Resposta do servidor:', xhr.responseText);
            }
        });
    }

    // Carrega a página inicial quando o documento estiver pronto
    $(document).ready(function() {
        exibirProdutos(produtos);
        filtrarProdutos(1);
    });

    $('#pesquisa').on('keyup', function () {
        filtrarProdutos(1);
    });

    $('#filtroFornecedor').on('change', function () {
        filtrarProdutos(1);
    });

    // Função para lidar com cliques na paginação
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = $(this).data('page_number');
        if (page) {
            filtrarProdutos(page);
        }
    });
</script>