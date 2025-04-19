<?php

class Cliente {
    private string $nome;
    private string $telefone;
    private string $email;
    private string $cartaoCredito;
    private ?Endereco $endereco = null;

    public function __construct(
        string $nome, 
        string $telefone, 
        string $email, 
        string $cartaoCredito,
    ) {
        $this->nome = $nome;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->cartaoCredito = $cartaoCredito;
    }

    // Getters
    public function getNome(): string {
        return $this->nome;
    }

    public function getTelefone(): string {
        return $this->telefone;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getCartaoCredito(): string {
        return $this->cartaoCredito;
    }

    public function getEndereco(): ?Endereco {
        return $this->endereco;
    }

    // Setters
    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setTelefone(string $telefone): void {
        $this->telefone = $telefone;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setCartaoCredito(string $cartaoCredito): void {
        $this->cartaoCredito = $cartaoCredito;
    }

    public function setEndereco(?Endereco $endereco): void {
        $this->endereco = $endereco;
    }
}