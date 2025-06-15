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