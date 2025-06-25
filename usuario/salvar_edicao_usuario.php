<?php
// Define headers para JSON
header('Content-Type: application/json');

// Desabilita exibição de erros para evitar que apareçam no JSON
error_reporting(0);
ini_set('display_errors', 0);

try {
    include_once '../fachada.php';
    include_once '../comum.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica se o usuário está logado e é admin
    if (!isset($_SESSION["usuario_id"])) {
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit();
    }

    $usuario = $factory->getUsuarioDao()->buscaPorId($_SESSION["usuario_id"]);
    if (!$usuario || !$usuario->isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado - usuário não é admin']);
        exit();
    }

    // Recebe os dados do formulário
    $userId = $_POST['user_id'] ?? null;
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $cpfCnpj = $_POST['cpf_cnpj'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $descricao = $_POST['descricao'] ?? '';

    if (!$userId || !$nome || !$email) {
        $erros = [];
        if (!$userId) $erros[] = 'ID do usuário';
        if (!$nome) $erros[] = 'Nome';
        if (!$email) $erros[] = 'Email';
        echo json_encode([
            'success' => false, 
            'message' => 'Dados obrigatórios não fornecidos: ' . implode(', ', $erros)
        ]);
        exit();
    }

    $usuarioDao = $factory->getUsuarioDao();
    $usuarioBuscado = $usuarioDao->buscaPorId($userId);
    
    if (!$usuarioBuscado) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit();
    }

    // Atualiza os dados do usuário
    $usuarioBuscado->setNome($nome);
    $usuarioBuscado->setEmail($email);
    $usuarioBuscado->setTelefone($telefone);
    
    // Atualiza a senha se fornecida
    if (!empty($senha)) {
        $senhaHash = md5($senha);
        $usuarioBuscado->setSenha($senhaHash);
    }
    
    // Salva as alterações do usuário
    $resultado = $usuarioDao->alteraDadosBasicos($usuarioBuscado);
    
    // Verifica diretamente no banco se foi salvo
    $sql = "SELECT tipo FROM usuario WHERE id = :id";
    $stmt = $factory->getConnection()->prepare($sql);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        // Se for fornecedor, atualiza ou cria os dados do fornecedor
        if (method_exists($usuarioBuscado, 'getTipo') && $usuarioBuscado->getTipo()) {
            $fornecedorDao = $factory->getFornecedorDao();
            $fornecedor = $fornecedorDao->buscaPorUsuarioId($userId);
            if ($fornecedor) {
                $fornecedor->setNome($nome);
                $fornecedor->setCnpj($cpfCnpj);
                $fornecedor->setDescricao($descricao);
                $resultadoFornecedor = $fornecedorDao->atualiza($fornecedor);
            } else {
                $fornecedorExistente = $fornecedorDao->buscaPorCnpj($cpfCnpj);
                if ($fornecedorExistente) {
                    echo json_encode(['success' => false, 'message' => 'Este CNPJ já está sendo usado por outro fornecedor']);
                    exit();
                }
                $sql = "INSERT INTO fornecedor (usuario_id, cnpj, descricao) VALUES (:usuario_id, :cnpj, :descricao)";
                $stmt = $factory->getConnection()->prepare($sql);
                $stmt->bindValue(":usuario_id", $userId, PDO::PARAM_INT);
                $stmt->bindValue(":cnpj", $cpfCnpj);
                $stmt->bindValue(":descricao", $descricao);
                try {
                    $resultadoInsert = $stmt->execute();
                    if (!$resultadoInsert) {
                        echo json_encode(['success' => false, 'message' => 'Erro ao criar fornecedor']);
                        exit();
                    }
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'unique') !== false || strpos($e->getMessage(), 'duplicate') !== false) {
                        echo json_encode(['success' => false, 'message' => 'Este CNPJ já está sendo usado por outro fornecedor']);
                        exit();
                    }
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar fornecedor: ' . $e->getMessage()]);
                    exit();
                }
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário no banco de dados']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Erro fatal: ' . $e->getMessage()]);
}
?> 