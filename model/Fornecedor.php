<?php

class Fornecedor extends Usuario {
    private $cnpj;
    private $descricao;

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

    
    
}
?>
