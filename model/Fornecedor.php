<?php

class Fornecedor extends Usuario {
    private $cnpj;
    private $descricao;

    // Construtor da classe
    public function __construct($nome, $email, $senha, $telefone, $cnpj, $descricao = null) {
        parent::__construct($nome, $email, $senha, $telefone, null);
        $this->cnpj = $cnpj;
        $this->descricao = $descricao;
    }

    // Getters e Setters específicos do Fornecedor
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

    
    
}
?>
