<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION["usuario_id"])) {
    header("Location: /ProgWebII/login/login.php");
    exit();
}

$usuario = $factory->getUsuarioDao()->buscaPorId($_SESSION["usuario_id"]);
if (!$usuario || !$usuario->isAdmin()) {
    header("Location: /ProgWebII/index.php");
    exit();
}

$page_title = "Gerenciar Permissões de Administrador";

// Adiciona os scripts necessários
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';

include_once '../layout_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Gerenciar Permissões de Administrador</h2>
            
            <div id="mensagem" class="alert" style="display: none;"></div>

            <div class="row mb-3">
                <div class="col-md-6 offset-md-6">
                    <div class="input-group input-group-sm">
                        <input type="text" id="pesquisa" class="form-control form-control-sm" placeholder="Pesquisar usuários...">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Administrador</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaUsuarios">
                        <!-- Os dados serão carregados via AJAX -->
                    </tbody>
                </table>
            </div>

            <div id="pagination" class="mt-3">
                <!-- A paginação será carregada via AJAX -->
            </div>

            <div class="mt-3">
                <a href="../index.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentPage = 1;
    let termo = '';

    function loadData(page = 1) {
        $.ajax({
            url: 'busca_usuarios_ajax.php',
            method: 'POST',
            data: {
                page: page,
                pesquisa: termo
            },
            success: function(data) {
                let html = '';
                
                data.usuarios.forEach(function(usuario) {
                    html += `
                        <tr>
                            <td>${usuario.id}</td>
                            <td>${usuario.nome}</td>
                            <td>${usuario.email}</td>
                            <td>${usuario.tipo ? 'Fornecedor' : 'Normal'}</td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input admin-checkbox" 
                                           data-user-id="${usuario.id}"
                                           ${usuario.is_admin ? 'checked' : ''}>
                                </div>
                            </td>
                            <td>
                                <a href="excluir_usuario.php?id=${usuario.id}" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                    <i class="fas fa-trash"></i> Excluir
                                </a>
                            </td>
                        </tr>
                    `;
                });
                
                $('#listaUsuarios').html(html);
                $('#pagination').html(data.pagination);
                
                // Reativa os event listeners dos checkboxes
                attachCheckboxListeners();
            }
        });
    }

    function attachCheckboxListeners() {
        $('.admin-checkbox').on('change', function() {
            const checkbox = $(this);
            const userId = checkbox.data('user-id');
            const isAdmin = checkbox.prop('checked');
            const action = isAdmin ? 'tornar administrador' : 'remover permissões de administrador';
            
            if (confirm(`Tem certeza que deseja ${action} deste usuário?`)) {
                $.ajax({
                    url: 'atualizar_permissao.php',
                    method: 'POST',
                    data: {
                        user_id: userId,
                        is_admin: isAdmin
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        $('#mensagem')
                            .removeClass('alert-success alert-danger')
                            .addClass(data.success ? 'alert-success' : 'alert-danger')
                            .html(data.message)
                            .show()
                            .delay(3000)
                            .fadeOut();
                    },
                    error: function() {
                        $('#mensagem')
                            .removeClass('alert-success alert-danger')
                            .addClass('alert-danger')
                            .html('Erro ao atualizar permissão')
                            .show()
                            .delay(3000)
                            .fadeOut();
                        checkbox.prop('checked', !isAdmin);
                    }
                });
            } else {
                checkbox.prop('checked', !isAdmin);
            }
        });
    }

    // listener para pesquisa
    $('#pesquisa').on('keyup', function() {
        termo = $(this).val();
        currentPage = 1;
        loadData(currentPage);
    });

    // listener para paginação
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page_number');
        if (page) {
            currentPage = page;
            loadData(page);
        }
    });

    // Carrega os dados iniciais
    loadData();
});
</script>

<?php include_once '../layout_footer.php'; ?>
