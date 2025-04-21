<?php

class Estoque {
    private $quantidade;
    private $preco;
    private Produto $produto;

    public function __construct(Produto $produto, $quantidade = 0, $preco = 0.0) {
        $this->produto = $produto;
        $this->quantidade = $quantidade;
        $this->preco = $preco;
    }

    // Getters
    public function getQuantidade() {
        return $this->quantidade;
    }

    public function getPreco() {
        return $this->preco;
    }

    public function getProduto() {
        return $this->produto;
    }

    // Setters
    public function setQuantidade($quantidade) {
        $this->quantidade = $quantidade;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setProduto($produto) {
        $this->produto = $produto;
    }

    // Métodos
    public function adicionarQuantidade(int $quantidade): void {
        $this->quantidade += $quantidade;
    }

    public function removerQuantidade(int $quantidade): void {
        if ($this->quantidade >= $quantidade) {
            $this->quantidade -= $quantidade;
        } else {
            throw new Exception("Quantidade insuficiente em estoque");
        }
    }

    public function calcularValorTotal() {
        return $this->quantidade * $this->preco;
    }
} 