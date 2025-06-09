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
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container mt-5">
    <div class="position-relative mb-4">
        <h3 class="text-center">Meus Produtos</h3>
        <a href="cadastra_produto.php" class="btn btn-success position-absolute end-0 top-0">Cadastrar Novo Produto</a>
    </div>

    <!-- Campo de Pesquisa -->
    <div class="mb-3">
        <input type="text" id="pesquisa" class="form-control" placeholder="Pesquisar produtos..." />
    </div>

    <table class="table table-striped" id="tabelaProdutos">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="listaProdutos">
            <!-- Os dados serão carregados via AJAX -->
        </tbody>
    </table>

    <div id="pagination" class="mt-3">
        <!-- A paginação será carregada via AJAX -->
    </div>
</div>




<script>
$(document).ready(function() {
    let atual = 1;
    let termo = '';

    function loadData(page = 1) {
        console.log('Carregando dados - Página:', page, 'Termo:', termo);
        $.ajax({
            url: 'busca_produtos_fornecedor_ajax.php',
            method: 'POST',
            data: {
                page: page,
                pesquisa: termo,
                fornecedor_id: <?php echo $fornecedor->getFornecedorId(); ?>
            },
            success: function(data) {
                console.log('Dados recebidos:', data);
                let html = '';
                
                if (data.produtos) {
                    // Decodifica o JSON se for uma string
                    let produtos = typeof data.produtos === 'string' ? JSON.parse(data.produtos) : data.produtos;
                    
                    if (produtos.length > 0) {
                        produtos.forEach(function(produto) {
                            html += `
                                <tr>
                                    <td>${produto.codigo || ''}</td>
                                    <td>${produto.nome}</td>
                                    <td>${produto.descricao}</td>
                                    <td>
                                        <a href="editar_produto.php?id=${produto.id}" class="btn btn-warning btn-sm">Editar</a>
                                        <a href="excluir_produto.php?id=${produto.id}" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center">Nenhum produto encontrado</td></tr>';
                    }
                } else {
                    html = '<tr><td colspan="4" class="text-center">Nenhum produto encontrado</td></tr>';
                }
                
                $('#listaProdutos').html(html);
                $('#pagination').html(data.pagination);
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição:', error);
                console.log('Status:', status);
                console.log('Resposta:', xhr.responseText);
                $('#listaProdutos').html('<tr><td colspan="4" class="text-center text-danger">Erro ao carregar produtos</td></tr>');
            }
        });
    }

    // listener para pesquisa
    $('#pesquisa').on('keyup', function() {
        termo = $(this).val();
        console.log('Termo de pesquisa:', termo);
        atual = 1;
        loadData(atual);
    });

    // Adiciona um listener para o evento de input também
    $('#pesquisa').on('input', function() {
        termo = $(this).val();
        console.log('Termo de pesquisa (input):', termo);
        atual = 1;
        loadData(atual);
    });

    // Adiciona um listener para o evento de change também
    $('#pesquisa').on('change', function() {
        termo = $(this).val();
        console.log('Termo de pesquisa (change):', termo);
        atual = 1;
        loadData(atual);
    });

    //listener para paginação
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page_number');
        if (page) {
            atual = page;
            loadData(page);
        }
    });

    // Carrega os dados iniciais
    loadData();
});
</script>

<?php include_once '../layout_footer.php'; ?>
