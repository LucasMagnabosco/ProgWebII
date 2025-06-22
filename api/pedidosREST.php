<?php
header('Content-Type: application/json');
require_once '../fachada.php';

$pedidoDao = $factory->getPedidoDao();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $pedido = $pedidoDao->buscarPorId($id);
            if ($pedido) {
                $usuarioDao = $factory->getUsuarioDao();
                $usuario = $usuarioDao->buscaPorId($pedido->getUsuarioId());
                $pedidoArr = $pedido->toJson();
                $pedidoArr['nomeUsuario'] = $usuario ? $usuario->getNome() : '';
                // Buscar subpedidos
                $subpedidos = $pedidoDao->buscarSubpedidos($pedido->getId());
                $fornecedorId = null;
                if (isset($_SESSION['is_fornecedor']) && $_SESSION['is_fornecedor']) {
                    $fornecedorDao = $factory->getFornecedorDao();
                    $fornecedor = $fornecedorDao->buscaPorUsuarioId($_SESSION['usuario_id']);
                    if ($fornecedor) {
                        $fornecedorId = $fornecedor->getFornecedorId() ?: $fornecedor->getId();
                    }
                }
                $pedidoArr['subpedidos'] = [];
                foreach ($subpedidos as $sub) {
                    if ($fornecedorId && $sub['fornecedor_id'] != $fornecedorId) continue;
                    // Buscar nome do fornecedor
                    $fornecedorObj = $factory->getFornecedorDao()->buscaPorId($sub['fornecedor_id']);
                    $fornecedorNome = $fornecedorObj ? $fornecedorObj->getNome() : $sub['fornecedor_id'];
                    // Buscar itens do subpedido
                    $stmt = $factory->getConnection()->prepare("SELECT ip.*, p.nome as produto_nome, p.foto as produto_imagem FROM itens_pedido ip JOIN produto p ON ip.produto_id = p.id WHERE ip.pedido_fornecedor_id = :subpedido_id");
                    $stmt->bindValue(':subpedido_id', $sub['id']);
                    $stmt->execute();
                    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pedidoArr['subpedidos'][] = [
                        'id' => $sub['id'],
                        'fornecedor_id' => $sub['fornecedor_id'],
                        'fornecedor_nome' => $fornecedorNome,
                        'status' => $sub['status'],
                        'total' => $sub['total'],
                        'itens' => $itens
                    ];
                }
                http_response_code(200);
                echo json_encode($pedidoArr);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Pedido não encontrado']);
            }
        } else {
            $termo = isset($_GET['termo']) ? $_GET['termo'] : '';
            $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
            $limite = isset($_GET['limite']) ? max(1, intval($_GET['limite'])) : 10;
            $inicio = ($pagina - 1) * $limite;
            $cliente = isset($_GET['cliente']) ? intval($_GET['cliente']) : null;
            $fornecedorIdParam = isset($_GET['fornecedor']) ? intval($_GET['fornecedor']) : null;

            if ($fornecedorIdParam) {
                // Requisição para um fornecedor específico
                $isFornecedor = isset($_SESSION['is_fornecedor']) && $_SESSION['is_fornecedor'];
                $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
                // Validação de segurança
                if ($isFornecedor) {
                    $fornecedorDao = $factory->getFornecedorDao();
                    $fornecedorSessao = $fornecedorDao->buscaPorUsuarioId($_SESSION['usuario_id']);
                    $fornecedorSessaoId = $fornecedorSessao ? ($fornecedorSessao->getFornecedorId() ?: $fornecedorSessao->getId()) : null;
                    if ($fornecedorIdParam != $fornecedorSessaoId) {
                        http_response_code(403);
                        echo json_encode(['erro' => 'Acesso negado.']);
                        exit;
                    }
                } elseif (!$isAdmin) {
                    http_response_code(403);
                    echo json_encode(['erro' => 'Acesso negado.']);
                    exit;
                }
                // Busca e contagem para fornecedor
                $json = $pedidoDao->buscarPedidosPorFornecedor($fornecedorIdParam, $inicio, $limite, $termo);
                $pedidos = json_decode($json, true);
                $stmtCount = $factory->getConnection()->prepare("SELECT COUNT(DISTINCT pf.id) as total FROM pedido_fornecedor pf JOIN itens_pedido ip ON ip.pedido_fornecedor_id = pf.id WHERE pf.fornecedor_id = :fornecedor_id");
                $stmtCount->bindValue(':fornecedor_id', $fornecedorIdParam);
                $stmtCount->execute();
                $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
                $totalRegistros = $rowCount ? (int)$rowCount['total'] : 0;
            } else {
                // Requisição padrão para cliente ou admin
                $totalRegistros = $pedidoDao->contarPedidos($termo, $cliente, null);
                $json = $pedidoDao->buscaTodosFormatados($inicio, $limite, $termo, null);
                $pedidos = json_decode($json, true);
                if ($cliente) {
                    $pedidos = array_filter($pedidos, function($p) use ($cliente) {
                        return $p['usuarioId'] == $cliente;
                    });
                    $pedidos = array_values($pedidos);
                }
            }
            
            $totalPaginas = ceil($totalRegistros / $limite);
            http_response_code(200);
            echo json_encode([
                'pedidos' => $pedidos,
                'totalPaginas' => $totalPaginas,
                'paginaAtual' => $pagina,
                'totalRegistros' => $totalRegistros
            ], JSON_PRETTY_PRINT);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados inválidos']);
            exit;
        }
        $pedido = new Pedido(null, $data['usuarioId'], $data['enderecoId'], $data['dataPedido'], $data['status'] ?? null, $data['total']);
        try {
            $novoId = $pedidoDao->salvar($pedido);
            http_response_code(201);
            echo json_encode(['id' => $novoId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => $e->getMessage()]);
        }
        break;
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não informado']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados inválidos']);
            exit;
        }
        $pedido = new Pedido($id, $data['usuarioId'], $data['enderecoId'], $data['dataPedido'], $data['status'] ?? null, $data['total']);
        try {
            $pedidoDao->atualizar($pedido);
            http_response_code(200);
            echo json_encode(['sucesso' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => $e->getMessage()]);
        }
        break;
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não informado']);
            exit;
        }
        try {
            $pedidoDao->excluir($id);
            http_response_code(204); // No Content
            // Não retorna corpo em 204
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => $e->getMessage()]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não suportado']);
        break;
}

