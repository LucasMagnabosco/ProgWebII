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

<style>
.modal-lg {
    max-width: 800px;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.alert {
    border-radius: 8px;
    border: none;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

/* Responsividade para mobile */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .row .col-md-6 {
        margin-bottom: 15px;
    }
    
    .form-label {
        font-size: 14px;
    }
    
    .form-control, .form-select {
        font-size: 14px;
    }
}
</style>

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

<!-- Modal Editar Usuário -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="edit_telefone" name="telefone">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                        <input type="text" class="form-control" id="edit_cpf_cnpj" name="cpf_cnpj">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_senha" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" class="form-control" id="edit_senha" name="senha">
                    </div>

                    <div class="mb-3" id="div_edit_descricao" style="display:none;">
                        <label for="edit_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEdicao()" id="btnSalvar">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<script>
let atual = 1;
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
                            <button class="btn btn-primary btn-sm me-1" onclick="editarUsuario(${usuario.id})">
                                <i class="fas fa-edit"></i> Editar
                            </button>
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

$(document).ready(function() {
    // listener para pesquisa
    $('#pesquisa').on('keyup', function() {
        termo = $(this).val();
        atual = 1;
        loadData(atual);
    });

    // listener para paginação
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
    
    // Adiciona event listener para o botão salvar
    $(document).on('click', '#btnSalvar', function() {
        salvarEdicao();
    });

    // Limpa o formulário quando o modal for fechado
    $('#modalEditarUsuario').on('hidden.bs.modal', function () {
        $('#formEditarUsuario')[0].reset();
    });
});

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

// Função para editar usuário
function editarUsuario(userId) {
    $.ajax({
        url: 'buscar_usuario_ajax.php',
        method: 'POST',
        data: { user_id: userId },
        success: function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                const usuario = data.usuario;
                
                // Preenche o modal com os dados do usuário
                $('#edit_user_id').val(data.usuario.id);
                $('#edit_nome').val(data.usuario.nome);
                $('#edit_cpf_cnpj').val(data.usuario.cpf_cnpj);
                $('#edit_email').val(data.usuario.email);
                $('#edit_telefone').val(data.usuario.telefone || '');
                $('#edit_senha').val('');
                
                // Se for fornecedor, mostra e preenche a descrição
                if (usuario.tipo == 1) {
                    $('#div_edit_descricao').show();
                    $('#edit_descricao').val(usuario.descricao || '');
                } else {
                    $('#div_edit_descricao').hide();
                    $('#edit_descricao').val('');
                }
                
                // Abre o modal
                $('#modalEditarUsuario').modal('show');
            } else {
                alert('Erro ao carregar dados do usuário: ' + data.message);
            }
        },
        error: function() {
            alert('Erro ao carregar dados do usuário');
        }
    });
}

// Função para salvar edição
function salvarEdicao() {
    const formData = new FormData(document.getElementById('formEditarUsuario'));
    
    // Validação básica
    const nome = formData.get('nome');
    const email = formData.get('email');
    const userId = formData.get('user_id');
    
    if (!userId || !nome || !email) {
        const camposFaltando = [];
        if (!userId) camposFaltando.push('ID do usuário');
        if (!nome) camposFaltando.push('Nome');
        if (!email) camposFaltando.push('Email');
        
        alert('Campos obrigatórios não preenchidos: ' + camposFaltando.join(', '));
        return;
    }
    
    // Validação de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Por favor, insira um email válido!');
        return;
    }
    
    // Se o campo descrição estiver visível, adiciona ao formData
    if ($('#div_edit_descricao').is(':visible')) {
        formData.set('descricao', $('#edit_descricao').val());
    } else {
        formData.delete('descricao');
    }
    
    $.ajax({
        url: 'salvar_edicao_usuario.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#mensagem')
                    .removeClass('alert-success alert-danger')
                    .addClass('alert-success')
                    .html(response.message)
                    .show()
                    .delay(3000)
                    .fadeOut();
                $('#modalEditarUsuario').modal('hide');
                loadData(); // Recarrega a lista
            } else {
                alert('Erro ao salvar: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Erro ao salvar alterações');
        }
    });
}
</script>

<?php include_once '../layout_footer.php'; ?>
