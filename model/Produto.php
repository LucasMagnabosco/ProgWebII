<?php

class Produto {
    private $id;
    private $nome;
    private $descricao;
    private $foto;
    private $fornecedor_id;

    public function __construct($nome, $descricao, $fornecedor_id, $foto = null, $id = null) {
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->fornecedor_id = $fornecedor_id;
        $this->foto = $foto;
        $this->id = $id;
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
}
