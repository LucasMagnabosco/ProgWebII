<?php
interface UsuarioDao {

    public function insere($usuario);
    public function remove($usuario);
    public function removePorId($id);
    public function altera($usuario);
    public function buscaPorId($id);
    public function buscaPorEmail($email);
    public function buscaTodos();
    public function buscaPorTipo($tipo);
    public function atualizarTipo($usuario, $novoTipo);
    public function atualizarStatusAdmin($usuario, $isAdmin);
}
?>