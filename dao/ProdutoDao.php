<?php

interface ProdutoDao {
    public function insere(Produto $produto): bool;
    public function buscaTodos(): array;
    public function buscaPorId(int $id): ?Produto;
    public function buscaPorCodigo(string $codigo, int $fornecedorId): ?Produto;
    public function remove(int $id): bool;
    public function atualiza(Produto $produto): bool;
    public function buscaPorFornecedor(int $fornecedorId): array;
}
