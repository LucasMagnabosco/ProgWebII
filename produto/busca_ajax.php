<?php

include_once '../fachada.php';

// procura usuários

$palavra = $_POST['pesquisa'];

$dao = $factory->getProdutoDao();

echo $dao->buscaFiltrada($palavra);

?>