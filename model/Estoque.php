<?php

class Estoque {
    private int $quantidade;
    private float $preco;
    private Produto $produto;

    public function __construct(Produto $produto, int $quantidade = 0, float $preco = 0.0) {
        $this->produto = $produto;
        $this->quantidade = $quantidade;
        $this->preco = $preco;
    }

    // Getters
    public function getQuantidade(): int {
        return $this->quantidade;
    }

    public function getPreco(): float {
        return $this->preco;
    }

    public function getProduto(): Produto {
        return $this->produto;
    }

    // Setters
    public function setQuantidade(int $quantidade): void {
        $this->quantidade = $quantidade;
    }

    public function setPreco(float $preco): void {
        $this->preco = $preco;
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

    public function calcularValorTotal(): float {
        return $this->quantidade * $this->preco;
    }
} 