<?php

class Produto {
    private $nome;
    private $descricao;
    private $foto;
    private ?Estoque $estoque = null;

    public function __construct($nome, $descricao, $foto) {
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->foto = $foto;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getDescricao(): string {
        return $this->descricao;
    }

    public function getFoto(): string {
        return $this->foto;
    }

    public function getEstoque(): ?Estoque {
        return $this->estoque;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setDescricao(string $descricao): void {
        $this->descricao = $descricao;
    }

    public function setFoto(string $foto): void {
        $this->foto = $foto;
    }

    public function setEstoque(Estoque $estoque): void {
        $this->estoque = $estoque;
    }
}