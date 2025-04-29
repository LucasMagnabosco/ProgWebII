<?php
interface FornecedorDao {
    public function buscaTodos();
    public function buscaPorId($id);
    public function buscaPorCnpj($cnpj);
    public function buscaPorUsuarioId($usuarioId);
    public function insere($fornecedor);
    public function atualiza($fornecedor);
    public function deleta($id);
}
?>
