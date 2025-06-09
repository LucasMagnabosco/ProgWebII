<?php
include_once '../fachada.php';
include_once '../comum.php';

$page_title = "Carrinho de Compras";
include_once '../layout_header.php';
?>

<div class="container mt-5">
    <h2>Carrinho de Compras</h2>
    
    <?php if (empty($_SESSION['carrinho'])): ?>
        <div class="alert alert-info">
            Seu carrinho está vazio. <a href="../visualiza_produtos.php">Continue comprando</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['carrinho'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nome']) ?></td>
                            <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                            <td>
                                <form action="carrinho.php" method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="action" value="atualizar">
                                    <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                    <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" 
                                           min="1" class="form-control form-control-sm" style="width: 70px">
                                    <button type="submit" class="btn btn-sm btn-outline-primary ms-2">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                            </td>
                            <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                            <td>
                                <form action="carrinho.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="remover">
                                    <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Remover
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>R$ <?= number_format(calcularTotal(), 2, ',', '.') ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="../produto/visualiza_produtos.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Continuar Comprando
            </a>
            <a href="checkout.php" class="btn btn-success">
                <i class="fas fa-shopping-cart"></i> Finalizar Compra
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../layout_footer.php'; ?> 