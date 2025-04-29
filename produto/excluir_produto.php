<!-- controller -->
<?php
include_once '../fachada.php';

if (!isset($_GET['id'])) {
    header("Location: produtos.php?msg=ID do produto não especificado&tipo=danger");
    exit;
}

$id = intval($_GET['id']);

try {
    $produtoDao = $factory->getProdutoDao();

    if ($produtoDao->remove($id)) {
        header("Location: produtos.php?msg=Produto excluído com sucesso&tipo=success");
    } else {
        header("Location: produtos.php?msg=Erro ao excluir o produto&tipo=danger");
    }
} catch (Exception $e) {
    header("Location: produtos.php?msg=" . urlencode("Erro: " . $e->getMessage()) . "&tipo=danger");
}
