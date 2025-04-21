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
    public function getId(): int {
        return $this->id;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getTelefone(): string {
        return $this->telefone;
    }

    public function getEndereco(): ?Endereco {
        return $this->endereco;
    }

    public function getTipo(): string {
        return $this->tipo;
    }

    public function getCartaoCredito(): ?string {
        return $this->cartaoCredito;
    }

    public function getDescricao(): ?string {
        return $this->descricao;
    }

    // Setters
    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setTelefone(string $telefone): void {
        $this->telefone = $telefone;
    }

    public function adicionarEndereco($endereco) {
        $this->endereco = $endereco;
        return $this;
    }

    public function setCartaoCredito(?string $cartaoCredito): void {
        $this->cartaoCredito = $cartaoCredito;
    }

    public function setDescricao(?string $descricao): void {
        $this->descricao = $descricao;
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