<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é um administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login/login.php?msg=Acesso restrito a administradores&tipo=warning");
    exit();
}

$page_title = "Gerenciar Pedidos";
include_once '../layout_header.php';

// Busca os pedidos
$pedidoDao = $factory->getPedidoDao();
$pedidos = $pedidoDao->buscarTodos();

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

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gerenciar Pedidos</h2>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Filtrar por Status
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?status=PENDENTE">Pendente</a></li>
                <li><a class="dropdown-item" href="?status=APROVADO">Aprovado</a></li>
                <li><a class="dropdown-item" href="?status=EM_PREPARACAO">Em Preparação</a></li>
                <li><a class="dropdown-item" href="?status=ENVIADO">Enviado</a></li>
                <li><a class="dropdown-item" href="?status=ENTREGUE">Entregue</a></li>
                <li><a class="dropdown-item" href="?status=CANCELADO">Cancelado</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?">Todos</a></li>
            </ul>
        </div>
    </div>
    
    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">
            Nenhum pedido encontrado.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo $pedido->getId(); ?></td>
                            <td>
                                <?php 
                                $usuarioDao = $factory->getUsuarioDao();
                                $usuario = $usuarioDao->buscarPorId($pedido->getClienteId());
                                echo htmlspecialchars($usuario->getNome());
                                ?>
                            </td>
                            <td><?php echo formatarData($pedido->getDataPedido()); ?></td>
                            <td>R$ <?php echo number_format($pedido->getTotal(), 2, ',', '.'); ?></td>
                            <td><?php echo formatarStatus($pedido->getStatus()); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#pedidoModal<?php echo $pedido->getId(); ?>">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success dropdown-toggle" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false">
                                        Atualizar Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="atualizar_status.php?id=<?php echo $pedido->getId(); ?>&status=APROVADO">Aprovar</a></li>
                                        <li><a class="dropdown-item" href="atualizar_status.php?id=<?php echo $pedido->getId(); ?>&status=EM_PREPARACAO">Em Preparação</a></li>
                                        <li><a class="dropdown-item" href="atualizar_status.php?id=<?php echo $pedido->getId(); ?>&status=ENVIADO">Enviar</a></li>
                                        <li><a class="dropdown-item" href="atualizar_status.php?id=<?php echo $pedido->getId(); ?>&status=ENTREGUE">Entregue</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="atualizar_status.php?id=<?php echo $pedido->getId(); ?>&status=CANCELADO">Cancelar</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Modal de Detalhes do Pedido -->
                        <div class="modal fade" id="pedidoModal<?php echo $pedido->getId(); ?>" tabindex="-1" aria-labelledby="pedidoModalLabel<?php echo $pedido->getId(); ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="pedidoModalLabel<?php echo $pedido->getId(); ?>">
                                            Detalhes do Pedido #<?php echo $pedido->getId(); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <h6>Informações do Cliente</h6>
                                                <p>
                                                    <strong>Nome:</strong> <?php echo htmlspecialchars($usuario->getNome()); ?><br>
                                                    <strong>Email:</strong> <?php echo htmlspecialchars($usuario->getEmail()); ?><br>
                                                    <strong>Telefone:</strong> <?php echo htmlspecialchars($usuario->getTelefone()); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Informações do Pedido</h6>
                                                <p>
                                                    <strong>Data:</strong> <?php echo formatarData($pedido->getDataPedido()); ?><br>
                                                    <strong>Status:</strong> <?php echo formatarStatus($pedido->getStatus()); ?><br>
                                                    <strong>Total:</strong> R$ <?php echo number_format($pedido->getTotal(), 2, ',', '.'); ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <h6>Itens do Pedido</h6>
                                        <div class="table-responsive">
                                            <?php 
                                            $subpedidos = $pedidoDao->buscarSubpedidos($pedido->getId());
                                            $fornecedorDao = $factory->getFornecedorDao();
                                            $isFornecedor = isset($_SESSION['is_fornecedor']) && $_SESSION['is_fornecedor'];
                                            $fornecedorLogadoId = null;
                                            if ($isFornecedor) {
                                                $fornecedorObj = $fornecedorDao->buscaPorUsuarioId($_SESSION['usuario_id']);
                                                $fornecedorLogadoId = $fornecedorObj ? ($fornecedorObj->getFornecedorId() ?: $fornecedorObj->getId()) : null;
                                            }
                                            foreach ($subpedidos as $sub) {
                                                if ($isFornecedor && $sub['fornecedor_id'] != $fornecedorLogadoId) continue;
                                                $fornecedor = $fornecedorDao->buscaPorId($sub['fornecedor_id']);
                                                echo '<div class="mb-3 p-2 border rounded">';
                                                echo '<strong>Fornecedor:</strong> ' . htmlspecialchars($fornecedor ? $fornecedor->getNome() : $sub['fornecedor_id']) . ' | ';
                                                echo '<strong>Status:</strong> ' . htmlspecialchars($sub['status']) . ' | ';
                                                echo '<strong>Total:</strong> R$ ' . number_format($sub['total'], 2, ',', '.') . '<br>';
                                                $stmt = $factory->getConnection()->prepare("SELECT ip.*, p.nome as produto_nome FROM itens_pedido ip JOIN produto p ON ip.produto_id = p.id WHERE ip.pedido_fornecedor_id = :subpedido_id");
                                                $stmt->bindValue(':subpedido_id', $sub['id']);
                                                $stmt->execute();
                                                $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                echo '<table class="table table-sm mt-2"><thead><tr><th>Produto</th><th>Quantidade</th><th>Preço Unitário</th><th>Subtotal</th></tr></thead><tbody>';
                                                if ($itens) {
                                                    foreach ($itens as $item) {
                                                        echo '<tr>';
                                                        echo '<td>' . htmlspecialchars($item['produto_nome']) . '</td>';
                                                        echo '<td>' . $item['quantidade'] . '</td>';
                                                        echo '<td>R$ ' . number_format($item['preco_unitario'], 2, ',', '.') . '</td>';
                                                        echo '<td>R$ ' . number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.') . '</td>';
                                                        echo '</tr>';
                                                    }
                                                } else {
                                                    echo '<tr><td colspan="4">Nenhum item encontrado para este subpedido.</td></tr>';
                                                }
                                                echo '</tbody></table></div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../layout_footer.php'; ?> 