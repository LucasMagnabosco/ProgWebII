<?php

class Produto {
    private $nome;
    private $descricao;
    private $foto;
    private $fornecedor_id;

    public function __construct($nome, $descricao, $foto = null, $fornecedor_id) {
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->foto = $foto;
        $this->fornecedor_id = $fornecedor_id;
    }

    // Getters e setters para os atributos
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
}
