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

<style>
    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }
        .table-responsive {
            margin: 0;
            border: none;
        }
        .table {
            margin-bottom: 1rem;
        }
        .table thead {
            display: none;
        }
        .table tbody tr {
            display: block;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .table tbody td {
            display: block;
            padding: 0.75rem 0;
            border: none;
            text-align: left;
        }
        .table tbody td:not(:last-child) {
            border-bottom: 1px solid #f0f0f0;
        }
        .table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            display: block;
            margin-bottom: 0.5rem;
            color: #444;
            font-size: 0.9rem;
        }
        .update-quantity-form {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }
        .quantity-input {
            width: 70px !important;
            text-align: center;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .btn-group-mobile {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
            padding: 0 0.5rem;
        }
        .btn-group-mobile .btn {
            width: 100%;
            padding: 0.875rem;
            font-size: 1rem;
            border-radius: 6px;
            font-weight: 500;
        }
        .table tfoot {
            display: block;
            margin-top: 1.5rem;
            padding: 0 0.5rem;
        }
        .table tfoot tr {
            display: block;
        }
        .table tfoot td {
            display: block;
            padding: 0.75rem 0;
        }
        .cart-total {
            font-size: 1.25rem;
            padding: 1.25rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: right;
            border: 1px solid #e0e0e0;
        }
        .btn-danger {
            margin-top: 0.75rem;
            padding: 0.75rem;
            font-size: 0.9rem;
            border-radius: 6px;
        }
        .btn-outline-primary {
            border-width: 2px;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
    }
</style>

<div class="container mt-5">
    <h2>Carrinho</h2>
    
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
                            <td data-label="Produto"><?= htmlspecialchars($item['nome']) ?></td>
                            <td data-label="Preço">R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                            <td data-label="Quantidade">
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
                            <td data-label="Subtotal" class="subtotal">R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                            <td data-label="Ações">
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
                        <td colspan="3" class="text-start"><strong>Total:</strong></td>
                        <td><strong id="cart-total" class="cart-total">R$ <?= number_format($total, 2, ',', '.') ?></strong></td>
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
        
        <div class="d-flex justify-content-between align-items-center">
            <a href="../visualiza_produtos.php" class="btn btn-secondary">Continuar Comprando</a>
            <a href="selecionar_endereco.php" class="btn btn-primary">Finalizar Compra</a>
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
            const originalValue = this.value;
            
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
                    // Restaura o valor original e mostra mensagem de erro
                    this.value = originalValue;
                    alert(data.msg || 'Erro ao atualizar quantidade');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                this.value = originalValue;
                alert('Erro ao atualizar quantidade');
            });
        });
    });


    document.querySelectorAll('.update-quantity-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = this.querySelector('.quantity-input');
            input.dispatchEvent(new Event('change'));
        });
    });
});
</script>

<?php include_once '../layout_footer.php'; ?> 