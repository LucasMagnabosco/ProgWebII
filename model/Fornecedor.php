<?php

class Fornecedor {
    private $id;
    private $nome;
    private $cnpj;
    private $telefone;
    private $email;
    private $endereco_id;

    // Construtor da classe
    public function __construct($id, $nome, $cnpj, $telefone, $email, $endereco_id) {
        $this->id = $id;
        $this->nome = $nome;
        $this->cnpj = $cnpj;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->endereco_id = $endereco_id;
    }

    // Getters e Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getCnpj() {
        return $this->cnpj;
    }

    public function setCnpj($cnpj) {
        $this->cnpj = $cnpj;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getEnderecoId() {
        return $this->endereco_id;
    }

    public function setEnderecoId($endereco_id) {
        $this->endereco_id = $endereco_id;
    }

    // Método para retornar uma representação textual do objeto
    public function __toString() {
        return "Fornecedor [ID: $this->id, Nome: $this->nome, CNPJ: $this->cnpj, Telefone: $this->telefone, Email: $this->email, Endereço ID: $this->endereco_id]";
    }
}
?>
