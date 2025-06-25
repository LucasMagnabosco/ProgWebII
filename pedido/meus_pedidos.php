<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php?msg=Por favor, faça login para ver seus pedidos&tipo=warning");
    exit();
}

$page_title = "Meus Pedidos";
include_once '../layout_header.php';

// Busca os pedidos do usuário
$pedidoDao = $factory->getPedidoDao();
$pedidos = $pedidoDao->buscarPorCliente($_SESSION['usuario_id']);

// Busca os pedidos do usuário
$usuario = $factory->getUsuarioDao()->buscaPorId($_SESSION['usuario_id']);
$isAdmin = $usuario && $usuario->isAdmin();
$isFornecedor = isset($_SESSION['is_fornecedor']) && $_SESSION['is_fornecedor'];

$fornecedorIdJs = null;
if ($isFornecedor) {
    $fornecedorDao = $factory->getFornecedorDao();
    $fornecedorObj = $fornecedorDao->buscaPorUsuarioId($_SESSION['usuario_id']);
    if ($fornecedorObj) {
        $fornecedorIdJs = $fornecedorObj->getFornecedorId() ?: $fornecedorObj->getId();
    }
}

$page_title = $isAdmin ? "Pedidos" : "Meus Pedidos";
include_once '../layout_header.php';

// Função para formatar a data
function formatarData($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Função para formatar o status
function formatarStatus($status) {
    $statusClasses = [
        'PENDENTE' => 'warning',
        'APROVADO' => 'info',
        'EM_PREPARACAO' => 'primary',
        'ENVIADO' => 'info',
        'ENTREGUE' => 'success',
        'CANCELADO' => 'danger'
    ];
    
    $statusLabels = [
        'PENDENTE' => 'Pendente',
        'APROVADO' => 'Aprovado',
        'EM_PREPARACAO' => 'Em Preparação',
        'ENVIADO' => 'Enviado',
        'ENTREGUE' => 'Entregue',
        'CANCELADO' => 'Cancelado'
    ];
    
    $classe = $statusClasses[$status] ?? 'secondary';
    $label = $statusLabels[$status] ?? $status;
    
    return "<span class='badge bg-{$classe}'>{$label}</span>";
}
?>

<style>
/* Estilos para o carrossel */
.carousel-container {
    position: relative;
    width: 100%;
    height: 300px;
    overflow: hidden;
    border-radius: 8px;
    margin: 20px 0;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.carousel-slide img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.carousel-slide img.main {
    width: 60%;
    height: 80%;
    z-index: 2;
}

.carousel-slide img.side {
    width: 30%;
    height: 60%;
    opacity: 0.7;
    z-index: 1;
}

.carousel-slide img.side.left {
    transform: translateX(-20px);
}

.carousel-slide img.side.right {
    transform: translateX(20px);
}

.carousel-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 50%;
    z-index: 3;
    font-size: 18px;
    transition: all 0.3s ease;
}

.carousel-button:hover {
    background: rgba(0, 0, 0, 0.7);
}

.carousel-prev {
    left: 10px;
}

.carousel-next {
    right: 10px;
}

.carousel-indicators {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 10px;
}

.carousel-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #ccc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-indicator.active {
    background: #007bff;
}

/* Estilos responsivos para mobile */
@media (max-width: 768px) {
    .carousel-container {
        height: 200px;
    }
    
    .carousel-slide img.main {
        width: 80%;
        height: 90%;
    }
    
    .carousel-slide img.side {
        width: 25%;
        height: 50%;
    }
    
    .carousel-button {
        padding: 8px 12px;
        font-size: 16px;
    }
    
    /* Cards responsivos para itens do pedido */
    .item-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
    }
    
    .item-card .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .item-card .item-title {
        font-weight: bold;
        color: #495057;
        font-size: 16px;
    }
    
    .item-card .item-price {
        font-weight: bold;
        color: #28a745;
        font-size: 14px;
    }
    
    .item-card .item-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        font-size: 14px;
    }
    
    .item-card .item-detail {
        display: flex;
        flex-direction: column;
    }
    
    .item-card .item-detail-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 12px;
        text-transform: uppercase;
    }
    
    .item-card .item-detail-value {
        color: #495057;
    }
    
    .item-card .item-description {
        margin-top: 10px;
        padding: 8px;
        background: white;
        border-radius: 4px;
        font-size: 13px;
        color: #6c757d;
    }
    
    /* Melhorias para controles de status em mobile */
    .status-controls {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }
    
    .status-controls .form-select {
        width: 100% !important;
        margin-left: 0 !important;
    }
    
    .status-controls .btn {
        width: 100%;
    }
    
    /* Modal responsivo */
    .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
    }
    
    .modal-body {
        padding: 15px;
    }
    
    /* Melhorias gerais para mobile */
    .container {
        padding: 10px;
    }
    
    .card {
        margin-bottom: 15px;
    }
    
    .card-header h5 {
        font-size: 16px;
    }
    
    .card-body p {
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    /* Melhorias para busca */
    .d-flex.align-items-center.gap-2 {
        flex-direction: column;
        gap: 10px !important;
    }
    
    .d-flex.align-items-center.gap-2 input {
        width: 100%;
    }
    
    .d-flex.align-items-center.gap-2 button {
        width: 100%;
    }
    
    /* Melhorias para paginação */
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination .page-link {
        padding: 8px 12px;
        font-size: 14px;
    }
    
    /* Melhorias para modal */
    .modal-title {
        font-size: 18px;
    }
    
    .modal-body {
        font-size: 14px;
    }
    
    .modal-body h6 {
        font-size: 16px;
        margin-top: 15px;
    }
    
    /* Melhorias para carrossel em mobile */
    .carousel-container {
        margin: 15px 0;
    }
    .carousel-indicators {
        margin-top: 8px;
    }
    .carousel-indicator {
        width: 8px;
        height: 8px;
    }
    
    /* Melhorias para cards de pedidos */
    .row .col-md-6 {
        width: 100%;
        margin-bottom: 15px;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .card-header {
        padding: 12px 15px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .card-body {
        padding: 15px;
    } 
    .card-text {
        line-height: 1.5;
    }
    .card-text strong {
        color: #495057;
    }
    
    /* Melhorias para badges de status */
    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }
    /* Melhorias para informações de data */
    small {
        font-size: 12px;
        line-height: 1.4;
    }
    
    /* Melhorias para controles de status em mobile */
    .status-controls {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
    } 
    .status-controls .form-select {
        border: 1px solid #ced4da;
        border-radius: 4px;
    } 
    .status-controls .btn {
        border-radius: 4px;
        font-weight: 500;
    }
}

/* Estilos para desktop */
@media (min-width: 769px) {
    .item-card {
        display: none; /* Esconde cards em desktop */
    }
    .table-responsive {
        display: block; /* Mostra tabela em desktop */
    }
}

/* Esconde tabela em mobile */
@media (max-width: 768px) {
    .table-responsive {
        display: none;
    }
    .item-card {
        display: block;
    }
}
</style>

<div class="container mt-4">
    <h2 class="mb-4"><?php echo $isAdmin ? 'Pedidos' : 'Meus Pedidos'; ?></h2>
    <div class="mb-3 d-flex align-items-center gap-2" style="max-width:600px;">
        <input type="text" id="busca-termo" class="form-control" placeholder="Buscar por cliente, número ou nome do pedido...">
        <button class="btn btn-secondary" onclick="carregarPedidos(1)">Buscar</button>
    </div>
    <div id="total-pedidos" class="mb-2"></div>
    <div id="pedidos-list"></div>
    <div class="paginacao" id="paginacao"></div>
    <div id="loading" style="display:none;text-align:center;"><span class="spinner-border"></span> Carregando...</div>
    <div id="erro-pedidos" class="alert alert-danger" style="display:none;"></div>
</div>

<!-- Modal Detalhe do Pedido -->
<div class="modal fade" id="modalDetalhe" tabindex="-1" aria-labelledby="modalDetalheLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetalheLabel">Detalhes do Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="detalhePedidoBody">
        <!-- AJAX -->
      </div>
    </div>
  </div>
</div>

<script>
const usuarioId = <?php echo json_encode($_SESSION['usuario_id']); ?>;
const isAdmin = <?php echo json_encode($isAdmin); ?>;
const isFornecedor = <?php echo json_encode($isFornecedor); ?>;
const fornecedorId = <?php echo json_encode($fornecedorIdJs); ?>;
const ITENS_API = '../api/pedidosREST.php';
const ITENS_DETALHE_API = '../api/pedidosREST.php';
const ITENS_POR_PAGINA = 4;
let paginaAtual = 1;
let debounceTimeout = null;

function carregarPedidos(pagina = 1) {
    document.getElementById('loading').style.display = '';
    document.getElementById('erro-pedidos').style.display = 'none';
    const termo = document.getElementById('busca-termo').value;
    let url = `${ITENS_API}?pagina=${pagina}&limite=${ITENS_POR_PAGINA}&termo=${encodeURIComponent(termo)}`;
    if (!isAdmin && !isFornecedor) {
        url += `&cliente=${usuarioId}`;
    }
    if (isFornecedor && fornecedorId && fornecedorId !== 'null' && fornecedorId !== null && fornecedorId !== undefined && fornecedorId !== '') {
        url += `&fornecedor=${fornecedorId}`;
    }
    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('Erro ao buscar pedidos');
            return r.json();
        })
        .then(res => {
            document.getElementById('loading').style.display = 'none';
            let pedidos = res && res.pedidos ? res.pedidos : [];
            if (isFornecedor && fornecedorId) {
                // Não precisa filtrar novamente, pois a API já retorna só os subpedidos do fornecedor
            }
            if (pedidos.length > 0) {
                renderPedidos(pedidos);
                renderPaginacao(res.totalPaginas, res.paginaAtual);
                document.getElementById('total-pedidos').innerText = `Total de pedidos: ${pedidos.length}`;
            } else {
                document.getElementById('pedidos-list').innerHTML = `<div class='alert alert-info'>Nenhum pedido encontrado.` + (isAdmin ? '' : (isFornecedor ? ' Você ainda não recebeu nenhum pedido.' : ' Você ainda não fez nenhum pedido.')) + `</div>`;
                document.getElementById('paginacao').innerHTML = '';
                document.getElementById('total-pedidos').innerText = 'Total de pedidos: 0';
            }
        })
        .catch(err => {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('erro-pedidos').style.display = '';
            document.getElementById('erro-pedidos').innerText = 'Erro ao buscar pedidos. Tente novamente.';
        });
}

function renderPedidos(pedidos) {
    let html = '<div class="row">';
    pedidos.forEach(pedido => {
        html += `
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pedido #${pedido.id}</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <strong>Data:</strong> ${formatarData(pedido.dataPedido)}<br>
                        <strong>Total:</strong> R$ ${parseFloat(pedido.total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </p>
                    <button class="btn btn-primary btn-sm" onclick="abrirDetalhe(${pedido.id})">Ver Detalhes</button>
                </div>
            </div>
        </div>
        `;
    });
    html += '</div>';
    document.getElementById('pedidos-list').innerHTML = html;
}

function renderPaginacao(totalPaginas, paginaAtual) {
    let html = '<nav aria-label="Navegação de páginas"><ul class="pagination justify-content-center">';
    // Botão Anterior
    if (paginaAtual > 1) {
        html += `<li class="page-item"><button class="page-link" onclick="carregarPedidos(${paginaAtual - 1})"><i class='fas fa-chevron-left'></i></button></li>`;
    }
    let page_array = [];
    if (totalPaginas > 5) {
        if (paginaAtual < 5) {
            for (let i = 1; i <= 5; i++) page_array.push(i);
            page_array.push('...');
            page_array.push(totalPaginas);
        } else if (paginaAtual > totalPaginas - 4) {
            page_array.push(1);
            page_array.push('...');
            for (let i = totalPaginas - 4; i <= totalPaginas; i++) page_array.push(i);
        } else {
            page_array.push(1);
            page_array.push('...');
            for (let i = paginaAtual - 1; i <= paginaAtual + 1; i++) page_array.push(i);
            page_array.push('...');
            page_array.push(totalPaginas);
        }
    } else {
        for (let i = 1; i <= totalPaginas; i++) page_array.push(i);
    }
    for (let i = 0; i < page_array.length; i++) {
        if (page_array[i] === '...') {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        } else if (page_array[i] === paginaAtual) {
            html += `<li class="page-item active"><span class="page-link">${page_array[i]}</span></li>`;
        } else {
            html += `<li class="page-item"><button class="page-link" onclick="carregarPedidos(${page_array[i]})">${page_array[i]}</button></li>`;
        }
    }
    // Botão Próximo
    if (paginaAtual < totalPaginas) {
        html += `<li class="page-item"><button class="page-link" onclick="carregarPedidos(${paginaAtual + 1})"><i class='fas fa-chevron-right'></i></button></li>`;
    }
    html += '</ul></nav>';
    document.getElementById('paginacao').innerHTML = html;
}

function abrirDetalhe(pedidoId) {
    document.getElementById('loading').style.display = '';
    fetch(`${ITENS_DETALHE_API}?id=${pedidoId}`)
        .then(r => {
            if (!r.ok) throw new Error('Erro ao buscar detalhes do pedido');
            return r.json();
        })
        .then(pedido => {
            document.getElementById('loading').style.display = 'none';
            let subpedidos = pedido.subpedidos || [];
            if (isFornecedor) {
                // Só mostrar subpedidos do fornecedor logado
                const fornecedorId = <?php echo isset($_SESSION['is_fornecedor']) && $_SESSION['is_fornecedor'] ? json_encode($factory->getFornecedorDao()->buscaPorUsuarioId($_SESSION['usuario_id'])->getFornecedorId() ?: $factory->getFornecedorDao()->buscaPorUsuarioId($_SESSION['usuario_id'])->getId()) : 'null'; ?>;
                subpedidos = subpedidos.filter(sub => sub.fornecedor_id == fornecedorId);
            }
            if (subpedidos.length > 0) {
                let todasImagens = [];
                const promises = [];

                (pedido.subpedidos || []).forEach(sub => {
    if (sub.itens && sub.itens.length > 0) {
        sub.itens.forEach(item => {
            const url = `get_imagem.php?id=${item.produto_id}`;
            promises.push(
                fetch(url)
                    .then(response => response.text())
                    .then(dataUrl => {
                        todasImagens.push(dataUrl.trim());
                        
                    })
                    .catch(err => {
                        console.error('Erro ao carregar imagem:', err);
                        todasImagens.push('../assets/imagem-default.jpg');
                                    })
                            );
                        });
                    }
                });

                Promise.all(promises).then(() => {

                    carrosselIndices[pedido.id] = 0;
                    let html = `<div><strong>Pedido #${pedido.id}</strong><br>
                        <strong>Data:</strong> ${formatarData(pedido.dataPedido)}<br>
                        <strong>Total:</strong> R$ ${parseFloat(pedido.total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}<br>
                        <strong>Cliente:</strong> ${pedido.nomeUsuario || pedido.usuarioId}</div><hr>`;
                    html += '<h6>Itens do Pedido:</h6>';
                    if (todasImagens.length > 0) {
                        html += `<div class='carousel-container' id='carousel-pedido-${pedido.id}'>`;
                        if (todasImagens.length > 1) {
                            html += `<button class='carousel-button carousel-prev' onclick='mudarSlide(${pedido.id}, -1)'>&lt;</button>`;
                        }
                        for (let idx = 0; idx < todasImagens.length; idx++) {
                            // Inicialmente, o primeiro é main, o último é left, o segundo é right
                            let classes = '';
                            let display = 'none';
                            if (idx === 0) {
                                classes = 'main';
                                display = 'flex';
                            } else if (idx === todasImagens.length - 1 && todasImagens.length > 1) {
                                classes = 'side left';
                                display = 'flex';
                            } else if (idx === 1 && todasImagens.length > 1) {
                                classes = 'side right';
                                display = 'flex';
                            }
                            html += `<div class='carousel-slide' data-idx='${idx}' style='display:${display};'>`;
                            html += `<img src="${todasImagens[idx]}" alt="Produto" class="${classes}" onerror="this.src='../assets/imagem-default.jpg'">`;
                            html += `</div>`;
                        }
                        if (todasImagens.length > 1) {
                            html += `<button class='carousel-button carousel-next' onclick='mudarSlide(${pedido.id}, 1)'>&gt;</button>`;
                        }
                        html += `</div>`;
                        if (todasImagens.length > 1) {
                            html += `<div class='carousel-indicators'>`;
                            for (let idx = 0; idx < todasImagens.length; idx++) {
                                html += `<span class='carousel-indicator${idx===0?' active':''}' onclick='irParaSlide(${pedido.id}, ${idx})'></span>`;
                            }
                            html += `</div>`;
                        }
                    }
                    // Renderiza o restante dos detalhes do pedido normalmente
                    (pedido.subpedidos || []).forEach(sub => {
                        html += `<div class='mb-3 p-2 border rounded'>`;
                        html += `<div class='mb-2'><strong>Fornecedor:</strong> ${sub.fornecedor_nome || sub.fornecedor_id} &nbsp; <strong>Status:</strong> <span id='status-subpedido-${sub.id}'>${formatarStatus(sub.status)}</span>`;
                        
                        // Adiciona informações de data quando disponíveis
                        if (sub.data_envio) {
                            html += `<br><small class='text-muted'><strong>Data de Envio:</strong> ${formatarData(sub.data_envio)}</small>`;
                        }
                        if (sub.data_cancelamento) {
                            html += `<br><small class='text-danger'><strong>Data de Cancelamento:</strong> ${formatarData(sub.data_cancelamento)}</small>`;
                        }
                        
                        if (isFornecedor) {
                            html += `<div class="status-controls">`;
                            html += `
                                <select id='novo-status-${sub.id}' class='form-select form-select-sm'>
                                    <option value='PENDENTE' ${sub.status==='PENDENTE'?'selected':''}>Pendente</option>
                                    <option value='APROVADO' ${sub.status==='APROVADO'?'selected':''}>Aprovado</option>
                                    <option value='EM_PREPARACAO' ${sub.status==='EM_PREPARACAO'?'selected':''}>Em Preparação</option>
                                    <option value='ENVIADO' ${sub.status==='ENVIADO'?'selected':''}>Enviado</option>
                                    <option value='ENTREGUE' ${sub.status==='ENTREGUE'?'selected':''}>Entregue</option>
                                    <option value='CANCELADO' ${sub.status==='CANCELADO'?'selected':''}>Cancelado</option>
                                </select>
                                <button class='btn btn-sm btn-outline-primary' onclick='alterarStatusSubpedido(${sub.id}, document.getElementById("novo-status-${sub.id}").value)'>Alterar Status</button>
                            `;
                            html += `</div>`;
                        }
                        html += `</div>`;
                        
                        // Tabela para desktop
                        html += `<div class="table-responsive">`;
                        html += `<table class='table table-sm mt-2'><thead><tr><th>Produto</th><th>Descrição</th><th>Quantidade</th><th>Preço Unitário</th><th>Subtotal</th></tr></thead><tbody>`;
                        let temItens = false;
                        if (sub.itens && sub.itens.length > 0) {
                            sub.itens.forEach(item => {
                                temItens = true;
                                html += `<tr>`;
                                html += `<td>${item.produto_nome}</td>`;
                                html += `<td>${item.produto_descricao || ''}</td>`;
                                html += `<td>${item.quantidade}</td>`;
                                html += `<td>R$ ${parseFloat(item.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>`;
                                html += `<td>R$ ${(item.quantidade * item.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>`;
                                html += `</tr>`;
                            });
                        }
                        if (!temItens) {
                            html += `<tr><td colspan='5'>Nenhum item encontrado para este fornecedor.</td></tr>`;
                        }
                        html += `</tbody></table>`;
                        html += `</div>`;
                        
                        // Cards responsivos para mobile
                        html += `<div class="item-cards">`;
                        if (sub.itens && sub.itens.length > 0) {
                            sub.itens.forEach(item => {
                                const subtotal = (item.quantidade * item.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                html += `<div class="item-card">`;
                                html += `<div class="item-header">`;
                                html += `<div class="item-title">${item.produto_nome}</div>`;
                                html += `<div class="item-price">R$ ${subtotal}</div>`;
                                html += `</div>`;
                                html += `<div class="item-details">`;
                                html += `<div class="item-detail">`;
                                html += `<div class="item-detail-label">Quantidade</div>`;
                                html += `<div class="item-detail-value">${item.quantidade}</div>`;
                                html += `</div>`;
                                html += `<div class="item-detail">`;
                                html += `<div class="item-detail-label">Preço Unit.</div>`;
                                html += `<div class="item-detail-value">R$ ${parseFloat(item.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>`;
                                html += `</div>`;
                                html += `</div>`;
                                if (item.produto_descricao) {
                                    html += `<div class="item-description">${item.produto_descricao}</div>`;
                                }
                                html += `</div>`;
                            });
                        } else {
                            html += `<div class="item-card">`;
                            html += `<div class="text-center text-muted">Nenhum item encontrado para este fornecedor.</div>`;
                            html += `</div>`;
                        }
                        html += `</div>`;
                        
                        html += `</div>`;
                    });
                    document.getElementById('detalhePedidoBody').innerHTML = html;
                    var modal = new bootstrap.Modal(document.getElementById('modalDetalhe'));
                    modal.show();
                });
            } else {
                let html = `<div><strong>Pedido #${pedido.id}</strong><br>
                        <strong>Data:</strong> ${formatarData(pedido.dataPedido)}<br>
                        <strong>Total:</strong> R$ ${parseFloat(pedido.total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}<br>
                        <strong>Cliente:</strong> ${pedido.nomeUsuario || pedido.usuarioId}</div><hr>`;
                html += '<h6>Itens do Pedido:</h6>';
                html += '<div class="alert alert-warning">Nenhum item encontrado para este pedido.</div>';
                document.getElementById('detalhePedidoBody').innerHTML = html;
                var modal = new bootstrap.Modal(document.getElementById('modalDetalhe'));
                modal.show();
            }
        })
        .catch(err => {
            document.getElementById('loading').style.display = 'none';
            alert('Erro ao buscar detalhes do pedido.');
        });
}

function formatarData(data) {
    const d = new Date(data);
    return d.toLocaleString('pt-BR');
}
function formatarStatus(status) {
    const map = {
        'PENDENTE': 'warning',
        'APROVADO': 'info',
        'EM_PREPARACAO': 'primary',
        'ENVIADO': 'info',
        'ENTREGUE': 'success',
        'CANCELADO': 'danger'
    };
    const label = {
        'PENDENTE': 'Pendente',
        'APROVADO': 'Aprovado',
        'EM_PREPARACAO': 'Em Preparação',
        'ENVIADO': 'Enviado',
        'ENTREGUE': 'Entregue',
        'CANCELADO': 'Cancelado'
    };
    return `<span class='badge bg-${map[status] || 'secondary'}'>${label[status] || status}</span>`;
}

function debounceCarregarPedidos() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => carregarPedidos(1), 400);
}

document.addEventListener('DOMContentLoaded', function() {
    carregarPedidos();
    document.getElementById('busca-termo').addEventListener('keyup', debounceCarregarPedidos);
});

//alterar status do subpedido
function alterarStatusSubpedido(subpedidoId, novoStatus) {
    if (!confirm('Tem certeza que deseja alterar o status?')) return;
    fetch('atualizar_status_subpedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subpedido_id: subpedidoId, status: novoStatus })
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            document.getElementById('status-subpedido-' + subpedidoId).innerHTML = formatarStatus(novoStatus);
            
            // Atualiza as informações de data se necessário
            const statusElement = document.getElementById('status-subpedido-' + subpedidoId);
            const parentDiv = statusElement.closest('.mb-2');
            
            // Remove informações de data existentes
            const existingDates = parentDiv.querySelectorAll('small');
            existingDates.forEach(date => date.remove());
            
            // Adiciona nova informação de data se aplicável
            const now = new Date();
            const formattedDate = now.toLocaleString('pt-BR');
            
            if (novoStatus === 'ENVIADO') {
                const dateElement = document.createElement('small');
                dateElement.className = 'text-muted d-block';
                dateElement.innerHTML = `<strong>Data de Envio:</strong> ${formattedDate}`;
                parentDiv.appendChild(dateElement);
            } else if (novoStatus === 'CANCELADO') {
                const dateElement = document.createElement('small');
                dateElement.className = 'text-danger d-block';
                dateElement.innerHTML = `<strong>Data de Cancelamento:</strong> ${formattedDate}`;
                parentDiv.appendChild(dateElement);
            }
            
            alert('Status alterado com sucesso!');
        } else {
            alert(res.erro || 'Erro ao alterar status.');
        }
    })
    .catch(() => alert('Erro ao alterar status.'));
}

// Adicionar funções JS para o carrossel
let carrosselIndices = {};
function mudarSlide(subId, dir) {
    const container = document.getElementById('carousel-pedido-' + subId);
    if (!container) return;
    const slides = container.querySelectorAll('.carousel-slide');
    const indicators = container.querySelectorAll('.carousel-indicator');
    if (!slides.length) return;
    if (typeof carrosselIndices[subId] !== 'number' || carrosselIndices[subId] < 0 || carrosselIndices[subId] >= slides.length) {
        carrosselIndices[subId] = 0;
    }
    let idx = carrosselIndices[subId];
    slides[idx].style.display = 'none';
    if (indicators[idx]) indicators[idx].classList.remove('active');
    // Remove classes de todos
    slides.forEach((slide, i) => {
        const img = slide.querySelector('img');
        if (img) img.className = '';
    });
    idx = (idx + dir + slides.length) % slides.length;
    slides.forEach((slide, i) => {
        slide.style.display = 'none';
    });
    // Central
    slides[idx].style.display = 'flex';
    const imgMain = slides[idx].querySelector('img');
    if (imgMain) imgMain.className = 'main';
    // Esquerda
    const leftIdx = (idx - 1 + slides.length) % slides.length;
    slides[leftIdx].style.display = 'flex';
    const imgLeft = slides[leftIdx].querySelector('img');
    if (imgLeft) imgLeft.className = 'side left';
    // Direita
    const rightIdx = (idx + 1) % slides.length;
    slides[rightIdx].style.display = 'flex';
    const imgRight = slides[rightIdx].querySelector('img');
    if (imgRight) imgRight.className = 'side right';
    if (indicators[idx]) indicators[idx].classList.add('active');
    carrosselIndices[subId] = idx;
}
function irParaSlide(subId, idx) {
    const container = document.getElementById('carousel-pedido-' + subId);
    if (!container) return;
    const slides = container.querySelectorAll('.carousel-slide');
    const indicators = container.querySelectorAll('.carousel-indicator');
    if (!slides.length) return;
    if (typeof carrosselIndices[subId] !== 'number' || carrosselIndices[subId] < 0 || carrosselIndices[subId] >= slides.length) {
        carrosselIndices[subId] = 0;
    }
    let atual = carrosselIndices[subId];
    slides.forEach((slide, i) => {
        slide.style.display = 'none';
        const img = slide.querySelector('img');
        if (img) img.className = '';
    });
    // Central
    slides[idx].style.display = 'flex';
    const imgMain = slides[idx].querySelector('img');
    if (imgMain) imgMain.className = 'main';
    // Esquerda
    const leftIdx = (idx - 1 + slides.length) % slides.length;
    slides[leftIdx].style.display = 'flex';
    const imgLeft = slides[leftIdx].querySelector('img');
    if (imgLeft) imgLeft.className = 'side left';
    // Direita
    const rightIdx = (idx + 1) % slides.length;
    slides[rightIdx].style.display = 'flex';
    const imgRight = slides[rightIdx].querySelector('img');
    if (imgRight) imgRight.className = 'side right';
    if (indicators[atual]) indicators[atual].classList.remove('active');
    if (indicators[idx]) indicators[idx].classList.add('active');
    carrosselIndices[subId] = idx;
}
</script>

<?php include_once '../layout_footer.php'; ?> 