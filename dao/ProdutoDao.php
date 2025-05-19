<?php

interface ProdutoDao {
    public function insere(Produto $produto);
    public function buscaTodos();
    public function buscaPorId($id);
    public function remove($id);
    public function atualiza(Produto $produto);
    public function buscaPorFornecedor($fornecedorId);
    public function buscaTodosFormatados();

    public function buscaFiltrada($termo);
    public function buscaPorFornecedorFormatados($fornecedorId);
    public function buscaFoto($id);
    public function contaComNome($nome);
    public function contaTodos();
    public function buscaTodosPaginado($inicio,$quantos);
}
