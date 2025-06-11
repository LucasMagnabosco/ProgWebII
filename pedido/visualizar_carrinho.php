<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$page_title = "Carrinho de Compras";
include_once '../layout_header.php';
?>

<div class="container mt-5">
    <h2>Carrinho de Compras</h2>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?= $_GET['tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
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
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['carrinho'] as $item): 
                        $subtotal = $item['preco'] * $item['quantidade'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nome']) ?></td>
                            <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                            <td>
                                <form action="carrinho.php" method="POST" class="d-flex align-items-center update-quantity-form">
                                    <input type="hidden" name="action" value="atualizar">
                                    <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                    <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" 
                                           min="1" class="form-control form-control-sm quantity-input" 
                                           style="width: 70px" data-produto-id="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary ms-2">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="subtotal">R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
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
                        <td><strong id="cart-total">R$ <?= number_format($total, 2, ',', '.') ?></strong></td>
                        <td>
                            <form action="carrinho.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="limpar">
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Tem certeza que deseja limpar o carrinho?')">
                                    <i class="fas fa-trash"></i> Limpar Carrinho
                                </button>
                            </form>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="../visualiza_produtos.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Continuar Comprando
            </a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="checkout.php" class="btn btn-success">
                    <i class="fas fa-shopping-cart"></i> Finalizar Compra
                </a>
            <?php else: ?>
                <a href="../login/login.php" class="btn btn-warning">
                    <i class="fas fa-sign-in-alt"></i> Faça login para finalizar a compra
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função para atualizar o total do carrinho
    function atualizarTotal() {
        fetch('calcular_total.php', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('cart-total').textContent = data.total_formatado;
        })
        .catch(error => console.error('Erro ao atualizar total:', error));
    }

    // Atualiza o total quando a quantidade é alterada
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const form = this.closest('form');
            const formData = new FormData(form);
            
            fetch('carrinho.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza o subtotal da linha
                    const row = this.closest('tr');
                    const subtotalCell = row.querySelector('.subtotal');
                    const preco = parseFloat(row.querySelector('td:nth-child(2)').textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
                    const quantidade = parseInt(this.value);
                    const subtotal = preco * quantidade;
                    subtotalCell.textContent = 'R$ ' + subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    // Atualiza o total do carrinho
                    atualizarTotal();
                } else {
                    alert(data.msg || 'Erro ao atualizar quantidade');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar quantidade');
            });
        });
    });
});
</script>

<?php include_once '../layout_footer.php'; ?> 