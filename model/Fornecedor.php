<?php

class Fornecedor {
    private string $nome;
    private string $descricao;
    private string $telefone;
    private string $email;
    private ?Endereco $endereco = null;

    public function __construct(string $nome, string $descricao, string $telefone, string $email) {
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->telefone = $telefone;
        $this->email = $email;
    }

   
    public function getNome(): string {
        return $this->nome;
    }

    public function getDescricao(): string {
        return $this->descricao;
    }

    public function getTelefone(): string {
        return $this->telefone;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getEndereco(): ?Endereco {
        return $this->endereco;
    }

  
    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setDescricao(string $descricao): void {
        $this->descricao = $descricao;
    }

    public function setTelefone(string $telefone): void {
        $this->telefone = $telefone;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setEndereco(?Endereco $endereco): void {
        $this->endereco = $endereco;
    }
}
