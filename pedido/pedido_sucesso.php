<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Verifica se foi fornecido um ID de pedido
if (!isset($_GET['id'])) {
    header("Location: visualizar_carrinho.php?msg=ID do pedido não fornecido&tipo=danger");
    exit();
}

$pedidoId = $_GET['id'];
$pedidoDao = $factory->getPedidoDao();
$pedido = $pedidoDao->buscarPorId($pedidoId);

// Verifica se o pedido existe e pertence ao usuário
if (!$pedido || $pedido->getUsuarioId() != $_SESSION['usuario_id']) {
    header("Location: ../visualiza_produtos.php?msg=Pedido não encontrado ou não pertence a você&tipo=danger");
    exit();
}

// Busca os itens do pedido
$itens = $pedidoDao->buscarItensPedido($pedidoId);

$page_title = "Pedido Realizado com Sucesso";
include_once '../layout_header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-check-circle"></i> Pedido Realizado com Sucesso!
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Obrigado pela sua compra!</h4>
                        <p>Seu pedido foi registrado com sucesso e está sendo processado.</p>
                    </div>

                    <div class="mb-4">
                        <h5>Detalhes do Pedido</h5>
                        <p><strong>Número do Pedido:</strong> #<?= $pedido->getId() ?></p>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($pedido->getDataPedido())) ?></p>
                        <p><strong>Status:</strong> <?= $pedido->getStatus() ?></p>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Preço Unitário</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['produto_nome']) ?></td>
                                        <td><?= $item['quantidade'] ?></td>
                                        <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                                        <td>R$ <?= number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>R$ <?= number_format($pedido->getTotal(), 2, ',', '.') ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <?php 
                        $subpedidos = $pedidoDao->buscarSubpedidos($pedido->getId());
                        $fornecedorDao = $factory->getFornecedorDao();
                        $isFornecedor = isset($_SESSION['is_fornecedor']) && $_SESSION['is_fornecedor'];
                        foreach ($subpedidos as $sub) {
                            $fornecedor = $fornecedorDao->buscaPorId($sub['fornecedor_id']);
                            if ($isFornecedor && $sub['fornecedor_id'] != $fornecedor->getFornecedorId()) continue;
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

                    <div class="text-center mt-4">
                        <a href="../visualiza_produtos.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Continuar Comprando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../layout_footer.php'; ?> 