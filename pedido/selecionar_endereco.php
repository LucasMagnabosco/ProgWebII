<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php?msg=Por favor, faça login para finalizar a compra&tipo=warning");
    exit();
}

// Verifica se o carrinho existe e não está vazio
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header("Location: visualizar_carrinho.php?msg=Seu carrinho está vazio&tipo=warning");
    exit();
}

$page_title = "Selecionar Endereço de Entrega";
include_once '../layout_header.php';

// Busca o usuário e seu endereço
$usuarioDao = $factory->getUsuarioDao();
$usuario = $usuarioDao->buscaPorId($_SESSION['usuario_id']);
$endereco = $usuario->getEndereco();
?>

<div class="container mt-4">
    <h2 class="mb-4">Selecionar Endereço de Entrega</h2>

    <?php if (!$endereco): ?>
        <div class="alert alert-warning">
            Você ainda não tem um endereço cadastrado.
            <a href="../endereco/insere_endereco.php?redirect=checkout" class="alert-link">Clique aqui</a> para cadastrar um endereço.
        </div>
    <?php else: ?>
        <form action="checkout.php" method="post" class="mb-4">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Endereço de Entrega</h5>
                        <a href="../endereco/novo_endereco.php?redirect=checkout" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> Adicionar Novo Endereço
                        </a>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="endereco_id" id="endereco_<?php echo $endereco->getId(); ?>" value="<?php echo $endereco->getId(); ?>" checked>
                        <label class="form-check-label" for="endereco_<?php echo $endereco->getId(); ?>">
                            <?php
                            echo htmlspecialchars($endereco->getRua()) . ", " . 
                                 htmlspecialchars($endereco->getNumero());
                            if ($endereco->getComplemento()) {
                                echo " - " . htmlspecialchars($endereco->getComplemento());
                            }
                            echo "<br>" . 
                                 htmlspecialchars($endereco->getBairro()) . ", " . 
                                 htmlspecialchars($endereco->getCidade()) . " - " . 
                                 htmlspecialchars($endereco->getEstado()) . "<br>" . 
                                 "CEP: " . htmlspecialchars($endereco->getCep());
                            ?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="visualizar_carrinho.php" class="btn btn-secondary">Voltar ao Carrinho</a>
                <button type="submit" class="btn btn-primary">Finalizar Compra</button>
            </div>
        </form>
    <?php endif; ?>
</div>

 