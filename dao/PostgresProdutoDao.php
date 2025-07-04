<?php

include_once('ProdutoDao.php');

class PostgresProdutoDao implements ProdutoDao
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function insere(Produto $produto)
    {
        try {
            $sql = "INSERT INTO produto (nome, descricao, fornecedor_id, quantidade, preco, foto, codigo) 
                VALUES (:nome, :descricao, :fornecedor_id, :quantidade, :preco, :foto, :codigo)";

            $stmt = $this->conn->prepare($sql);

            // Bind dos parâmetros
            $stmt->bindValue(':nome', $produto->getNome());
            $stmt->bindValue(':descricao', $produto->getDescricao());
            $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId(), PDO::PARAM_INT);
            $stmt->bindValue(':quantidade', $produto->getQuantidade(), PDO::PARAM_INT);
            $stmt->bindValue(':preco', $produto->getPreco());
            $stmt->bindValue(':codigo', $produto->getCodigo());

            // Tratamento especial para imagem como BYTEA (binário)
            $stmt->bindValue(':foto', $produto->getFoto(), PDO::PARAM_LOB);

            $result = $stmt->execute();

            if (!$result) {
                return false;
            }

            return true;

        } catch (PDOException $e) {
            return false;
        }
    }

    public function buscaPorId($id)
    {
        $sql = "SELECT * FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (isset($row['foto']) && is_resource($row['foto'])) {
                $row['foto'] = stream_get_contents($row['foto']);
            }
            return new Produto(
                $row['nome'],
                $row['descricao'],
                $row['fornecedor_id'],
                $row['preco'],
                $row['quantidade'],
                $row['foto'],
                $row['id'],
                $row['codigo']
            );
        }

        return null;
    }


    public function remove($id)
    {
        $sql = "DELETE FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function atualiza(Produto $produto)
    {
        // Verifica se a foto foi fornecida
        $temFoto = !is_null($produto->getFoto());

        // Monta SQL dinamicamente
        $sql = "UPDATE produto SET 
                nome = :nome, 
                descricao = :descricao, 
                preco = :preco, 
                quantidade = :quantidade, 
                codigo = :codigo";

        if ($temFoto) {
            $sql .= ", foto = :foto";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        // Bind dos parâmetros obrigatórios
        $stmt->bindValue(':nome', $produto->getNome());
        $stmt->bindValue(':descricao', $produto->getDescricao());
        $stmt->bindValue(':preco', $produto->getPreco());
        $stmt->bindValue(':quantidade', $produto->getQuantidade(), PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $produto->getCodigo());
        $stmt->bindValue(':id', $produto->getId(), PDO::PARAM_INT);

        // Bind da foto apenas se existir
        if ($temFoto) {
            $stmt->bindValue(':foto', $produto->getFoto(), PDO::PARAM_LOB);
        }

        return $stmt->execute();
    }

    public function buscaTodos()
    {
        $sql = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    p.preco, p.quantidade, p.fornecedor_id
                FROM produto p";
        $stmt = $this->conn->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $produtos = array();

        foreach ($rows as $row) {
            $produto = new Produto(
                $row['nome'],
                $row['descricao'],
                $row['fornecedor_id'],
                $row['preco'],
                $row['quantidade'],
                $row['foto'],
                $row['id'],
                $row['codigo']
            );
            $produtos[] = $produto;
        }
        return $produtos;
    }

    public function buscaTodosPaginado($inicio, $quantos, $termo = '') {
        $query = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    p.preco, p.quantidade, p.fornecedor_id
                    FROM produto p";
        
        if (!empty($termo)) {
            $query .= " WHERE UPPER(p.codigo) LIKE :termo 
                       OR UPPER(p.nome) LIKE :termo";
        }
        
        $query .= " ORDER BY id ASC
                    LIMIT :limit OFFSET :offset";
     
        $stmt = $this->conn->prepare($query);
        
        if (!empty($termo)) {
            $stmt->bindValue(':termo', '%' . strtoupper($termo) . '%');
        }
        
        $stmt->bindValue(':limit', $quantos, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $inicio, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $produtos = array();

        foreach ($rows as $row) {
            if (isset($row['foto']) && is_resource($row['foto'])) {
                $row['foto'] = stream_get_contents($row['foto']);
            }
            $produto = new Produto(
                $row['nome'],
                $row['descricao'],
                $row['fornecedor_id'],
                $row['preco'],
                $row['quantidade'],
                $row['foto'],
                $row['id'],
                $row['codigo']
            );
            $produtos[] = $produto;
        }
        return $produtos;
    }

    public function buscaTodosFormatados($inicio, $quantos, $termo = '')
    {
        $produtos = $this->buscaTodosPaginado($inicio, $quantos, $termo);
        $produtosJSON = [];
        foreach ($produtos as $produto) {
            $produtosJSON[] = $produto->toJson();
        }
        return json_encode($produtosJSON, JSON_PRETTY_PRINT);
    }

    public function buscaPorFornecedor($fornecedorId, $inicio = 0, $quantos = 5, $termo = '')
    {
        $sql = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    p.preco, p.quantidade, p.fornecedor_id
                FROM produto p
                WHERE p.fornecedor_id = :fornecedor_id";
        
        if (!empty($termo)) {
            $sql .= " AND (UPPER(p.nome) LIKE :termo 
                      OR UPPER(p.codigo) LIKE :termo 
                      OR UPPER(p.descricao) LIKE :termo)";
        }
        
        $sql .= " ORDER BY p.nome
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':fornecedor_id', $fornecedorId);
        
        if (!empty($termo)) {
            $stmt->bindValue(':termo', '%' . strtoupper($termo) . '%');
        }
        
        $stmt->bindValue(':limit', $quantos, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $inicio, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $produtos = array();
        foreach ($rows as $row) {
            if (isset($row['foto']) && is_resource($row['foto'])) {
                $row['foto'] = stream_get_contents($row['foto']);
            }
            $produto = new Produto(
                $row['nome'],
                $row['descricao'],
                $row['fornecedor_id'],
                $row['preco'],
                $row['quantidade'],
                $row['foto'],
                $row['id'],
                $row['codigo']
            );
            $produtos[] = $produto;
        }

        return $produtos;
    }

    public function buscaPorFornecedorFormatados($fornecedorId, $inicio = 0, $quantos = 5, $termo = '')
    {
        $produtos = $this->buscaPorFornecedor($fornecedorId, $inicio, $quantos, $termo);
        $produtosJSON = [];
        foreach ($produtos as $produto) {
            $produtosJSON[] = $produto->toJson();
        }
        return json_encode($produtosJSON, JSON_PRETTY_PRINT);
    }

    public function buscaFiltrada($termo, $inicio, $quantos)
    {
        $query = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    p.preco, p.quantidade, p.fornecedor_id
                FROM produto p
                WHERE UPPER(p.codigo) LIKE :termo 
                   OR UPPER(p.nome) LIKE :termo
                   OR UPPER(p.descricao) LIKE :termo
                ORDER BY id ASC
                LIMIT :limit OFFSET :offset";
     
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':termo', '%' . strtoupper($termo) . '%');
        $stmt->bindValue(':limit', $quantos, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $inicio, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $produtos = array();

        foreach ($rows as $row) {
            if (isset($row['foto']) && is_resource($row['foto'])) {
                $row['foto'] = stream_get_contents($row['foto']);
            }
            $produto = new Produto(
                $row['nome'],
                $row['descricao'],
                $row['fornecedor_id'],
                $row['preco'],
                $row['quantidade'],
                $row['foto'],
                $row['id'],
                $row['codigo']
            );
            $produtos[] = $produto;
        }

        $produtosJSON = [];
        foreach ($produtos as $produto) {
            $produtosJSON[] = $produto->toJson();
        }
        return $produtosJSON;
    }

    public function contaTodos() {

        $quantos = 0;

        $query = "SELECT COUNT(*) AS contagem FROM produto";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $quantos = $contagem;
        }
        
        return $quantos;
    }

    public function contaComNome($nome) {
        $quantos = 0;
        $query = "SELECT COUNT(*) AS contagem FROM produto
        WHERE UPPER(nome) LIKE ? ";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, '%' . strtoupper($nome) . '%');
        
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $quantos = $contagem;
        }
        
        return $quantos;
    }

    public function contaPorFornecedor($fornecedor_id) {
        $quantos = 0;
        $query = "SELECT COUNT(*) AS contagem FROM produto WHERE fornecedor_id = :fornecedor_id";
     
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':fornecedor_id', $fornecedor_id);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $quantos = $contagem;
        }
        
        return $quantos;
    }

    public function contaPorFornecedorENome($fornecedor_id, $nome) {
        $quantos = 0;
        $query = "SELECT COUNT(*) AS contagem FROM produto 
                 WHERE fornecedor_id = :fornecedor_id 
                 AND (UPPER(nome) LIKE :nome 
                      OR UPPER(codigo) LIKE :nome 
                      OR UPPER(descricao) LIKE :nome)";
     
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':fornecedor_id', $fornecedor_id);
        $stmt->bindValue(':nome', '%' . strtoupper($nome) . '%');
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $quantos = $contagem;
        }
        
        return $quantos;
    }

    public function getFotoPorId($id) {
        $sql = "SELECT foto FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && isset($row['foto']) ? $row['foto'] : null;
    }

}
