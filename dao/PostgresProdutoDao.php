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
                error_log("Erro ao inserir produto: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            return true;

        } catch (PDOException $e) {
            error_log("Exceção ao inserir produto: " . $e->getMessage());
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

    public function buscaTodosPaginado($inicio,$quantos) {
        $usuarios = array();

        $query = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    p.preco, p.quantidade, p.fornecedor_id
                    FROM produto p" . 
                    " ORDER BY id ASC" .
                    " LIMIT ? OFFSET ?";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $quantos);
        $stmt->bindParam(2, $inicio);
        $stmt->execute();

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

    public function buscaTodosFormatados($inicio,$quantos)
    {
        $produtos = $this->buscaTodosPaginados($inicio,$quantos);
        $produtosJSON = [];
        foreach ($produtos as $produto) {
            $produtosJSON[] = $produto->toJson();
        }
        return json_encode($produtosJSON, JSON_PRETTY_PRINT);
    }

    public function buscaPorFornecedor($fornecedorId)
    {
        $sql = "SELECT 
                    p.id, p.nome, p.descricao, p.foto, p.codigo,
                    p.preco, p.quantidade, p.fornecedor_id
                FROM produto p
                WHERE p.fornecedor_id = :fornecedor_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':fornecedor_id' => $fornecedorId]);
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
    public function buscaPorFornecedorFormatados($fornecedorId)
    {
        $produtos = $this->buscaPorFornecedor($fornecedorId);
        $produtosJSON = [];
        foreach ($produtos as $produto) {
            $produtosJSON[] = $produto->toJson();
        }
        return json_encode($produtosJSON, JSON_PRETTY_PRINT);
    }

    public function buscaFiltrada($nome,$inicio,$quantos)
    {
        $produtos = array();

        $sql = "SELECT * FROM produto 
                WHERE codigo ILIKE :termo 
                OR nome ILIKE :termo".
                " ORDER BY id ASC" .
                " LIMIT ? OFFSET ?";;

        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, '%' . strtoupper($nome) . '%');
        $stmt->bindValue(2, $quantos);
        $stmt->bindValue(3, $inicio);                $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filter_query = $query . "LIMIT " .$quantos. " OFFSET " . $inicio . '';
        error_log("---> DAO Query : " . $filter_query);

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
            $produtos[] = $produto->toJson();
            as
        }

        return json_encode($produtos, JSON_PRETTY_PRINT);
    }

    public function buscaFoto($id)
    {
        $query = "SELECT foto FROM produto WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || $result['foto'] === null) {
            return null;
        }

        $foto = $result['foto'];

        // Se $foto for um resource, leia seu conteúdo:
        if (is_resource($foto)) {
            $foto = stream_get_contents($foto);
        }

        return $foto;
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

}
