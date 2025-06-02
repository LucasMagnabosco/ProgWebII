<?php

class Produto {
    private $id;
    private $nome;
    private $descricao;
    private $foto;
    private $preco;
    private $quantidade;
    private $fornecedor_id;
    private $codigo;

    public function __construct($nome, $descricao, $fornecedor_id, $preco, $quantidade, $foto = null, $id = null, $codigo = null) {
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->fornecedor_id = $fornecedor_id;
        $this->preco = $preco;
        $this->quantidade = $quantidade;
        $this->foto = $foto;
        $this->id = $id;
        $this->codigo = $codigo;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getFoto() {
        return $this->foto;
    }

    public function getFornecedorId() {
        return $this->fornecedor_id;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setFoto($foto) {
        $this->foto = $foto;
    }

    public function setFornecedorId($fornecedor_id) {
        $this->fornecedor_id = $fornecedor_id;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function getPreco() {
        return $this->preco;
    }

    public function getQuantidade() {
        return $this->quantidade;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setQuantidade($quantidade) {
        $this->quantidade = $quantidade;
    }

    public function toJson(): array {
        $foto = $this->foto;
        if (is_resource($foto)) {
            $foto = stream_get_contents($foto);
        }
        
        $fotoBase64 = null;
        $fotoTipo = null;
        
        if ($foto) {
            // Detecta o tipo da imagem
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fotoTipo = $finfo->buffer($foto);
            $fotoBase64 = base64_encode($foto);
        }
        
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco' => $this->preco,
            'quantidade' => $this->quantidade,
            'fornecedor_id' => $this->fornecedor_id,
            'codigo' => $this->codigo ?? 'Não informado',
            'foto' => $fotoBase64,
            'foto_tipo' => $fotoTipo
        ];
    }
}
