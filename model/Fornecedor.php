<?php

class Fornecedor extends Usuario {
    private $cnpj;
    private $descricao;
    private $fornecedor_id;

    // Construtor da classe
    public function __construct($nome, $email, $senha, $telefone, $cnpj, $descricao = null) {
        parent::__construct($nome, $email, $senha, $telefone);
        $this->cnpj = $cnpj;
        $this->descricao = $descricao;
    }

    public function getCnpj() {
        return $this->cnpj;
    }

    public function setCnpj($cnpj) {
        $this->cnpj = $cnpj;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function getFornecedorId() {
        return $this->fornecedor_id;
    }

    public function setFornecedorId($fornecedor_id) {
        $this->fornecedor_id = $fornecedor_id;
    }
}
?>
