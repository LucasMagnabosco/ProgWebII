<?php

interface ProdutoDao {
    public function insere(Produto $produto);
    public function buscaTodos();
    public function buscaPorId($id);
    public function remove($id);
    public function atualiza(Produto $produto);
    public function buscaPorFornecedor($fornecedorId);
    public function buscaTodosFormatados($inicio,$quantos);
    public function buscaTodosPaginado($inicio,$quantos);

    public function buscaFiltrada($termo,$inicio,$quantos);
    public function contaComNome($nome);
    public function contaTodos();
}
