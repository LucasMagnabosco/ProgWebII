<?php

class Usuario {
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $telefone;
    private $endereco = null;
    private $tipo; // 'cliente' ou 'fornecedor'
    private $cartaoCredito = null;
    private $descricao = null;

    public function __construct(
        $nome,
        $email,
        $senha,
        $telefone,
        $tipo,
        ?string $cartaoCredito = null,
        ?string $descricao = null,
        ?Endereco $endereco = null
    ) {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->telefone = $telefone;
        $this->tipo = $tipo;
        $this->cartaoCredito = $cartaoCredito;
        $this->descricao = $descricao;
        $this->endereco = $endereco;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function getEndereco(): ?Endereco {
        return $this->endereco;
    }

    public function getTipo(){
        return $this->tipo;
    }

    public function getCartaoCredito() {
        return $this->cartaoCredito;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getSenha() { return $this->senha; }

    // Setters
    public function setNome(string $nome) {
        $this->nome = $nome;
    }

    public function setEmail(string $email) {
        $this->email = $email;
    }

    public function setTelefone(string $telefone) {
        $this->telefone = $telefone;
    }

    public function setSenha($senha) {
        $this->senha = md5($senha);
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function adicionarEndereco($endereco) {
        $this->endereco = $endereco;
        return $this;
    }

    public function setCartaoCredito(?string $cartaoCredito) {
        $this->cartaoCredito = $cartaoCredito;
    }

    public function setDescricao(?string $descricao) {
        $this->descricao = $descricao;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    // Verificação de tipo
    public function isCliente(): bool {
        return $this->tipo === 'cliente';
    }

    public function isFornecedor(): bool {
        return $this->tipo === 'fornecedor';
    }
}
?>