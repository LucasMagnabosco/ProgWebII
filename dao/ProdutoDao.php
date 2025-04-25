<?php

interface ProdutoDao {
    public function insere(Produto $produto): bool;
    public function buscaTodos(): array;
    public function buscaPorId(int $id): ?Produto;
}
