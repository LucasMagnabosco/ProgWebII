<?php
session_start();
include_once '../model/Produto.php';
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

// Função para calcular o total do carrinho
function calcularTotal() {
    $total = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
    return $total;
}

// Função para enviar resposta JSON
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
function jsonResponse($success, $msg = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'msg' => $msg
    ], $data));
    exit();
}

// Processa as ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $produto_id = $_POST['produto_id'] ?? 0;
    $quantidade = $_POST['quantidade'] ?? 0;

    switch ($action) {
        case 'adicionar':
            $produtoDao = $factory->getProdutoDao();
            $produto = $produtoDao->buscaPorId($produto_id);

            if ($produto && $produto->getQuantidade() >= $quantidade) {
                // Verifica se o produto já está no carrinho
                if (isset($_SESSION['carrinho'][$produto_id])) {
                    // Atualiza a quantidade se não exceder o estoque
                    $nova_quantidade = $_SESSION['carrinho'][$produto_id]['quantidade'] + $quantidade;
                    if ($nova_quantidade <= $produto->getQuantidade()) {
                        $_SESSION['carrinho'][$produto_id]['quantidade'] = $nova_quantidade;
                    } else {
                        if ($isAjax) {
                            jsonResponse(false, 'Quantidade indisponível em estoque');
                        } else {
                            header("Location: ../produto/detalhes_produto.php?id=$produto_id&msg=Quantidade indisponível em estoque&tipo=danger");
                            exit();
                        }
                    }
                } else {
                    // Adiciona o produto ao carrinho
                    $_SESSION['carrinho'][$produto_id] = [
                        'id' => $produto->getId(),
                        'nome' => $produto->getNome(),
                        'preco' => $produto->getPreco(),
                        'quantidade' => $quantidade
                    ];
                }
                if ($isAjax) {
                    jsonResponse(true, 'Produto adicionado ao carrinho');
                } else {
                    header("Location: ../produto/detalhes_produto.php?id=$produto_id&msg=Produto adicionado ao carrinho&tipo=success");
                }
            } else {
                if ($isAjax) {
                    jsonResponse(false, 'Produto indisponível');
                } else {
                    header("Location: ../produto/detalhes_produto.php?id=$produto_id&msg=Produto indisponível&tipo=danger");
                }
            }
            break;

        case 'atualizar':
            if (isset($_SESSION['carrinho'][$produto_id])) {
                // Busca o produto para verificar o estoque
                $produtoDao = $factory->getProdutoDao();
                $produto = $produtoDao->buscaPorId($produto_id);

                if ($produto && $quantidade <= $produto->getQuantidade()) {
                    $_SESSION['carrinho'][$produto_id]['quantidade'] = $quantidade;
                    
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true]);
                        exit;
                    }
                    
                    header('Location: visualizar_carrinho.php?msg=Quantidade atualizada com sucesso&tipo=success');
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'msg' => 'Quantidade indisponível em estoque'
                        ]);
                        exit;
                    }
                    
                    header('Location: visualizar_carrinho.php?msg=Quantidade indisponível em estoque&tipo=danger');
                }
            }
            break;

        case 'remover':
            if (isset($_SESSION['carrinho'][$produto_id])) {
                unset($_SESSION['carrinho'][$produto_id]);
            }
            if (!$isAjax) {
                header("Location: visualizar_carrinho.php");
            }
            break;
    }
    if ($isAjax) {
        jsonResponse(true);
    }
}
?> 