<?php
$page_title = "Inserção de Usuário";
// layout do cabeçalho
include_once "layout_header.php";
?>
<section>
<form action="insere_usuario.php" method="post">
    <table class='table table-hover table-responsive table-bordered'>
        <tr>
            <td>Nome</td>
            <td><input type='text' name='nome' class='form-control' required /></td>
        </tr>
        <tr>
            <td>Email</td>
            <td><input type='email' name='email' class='form-control' required /></td>
        </tr>
        <tr>
            <td>Senha</td>
            <td><input type='password' name='senha' class='form-control' required /></td>
        </tr>
        <tr>
            <td>Telefone</td>
            <td><input type='tel' name='telefone' class='form-control' required /></td>
        </tr>
        <tr>
            <td>Tipo</td>
            <td>
                <select name='tipo' class='form-control' required>
                    <option value='cliente'>Cliente</option>
                    <option value='fornecedor'>Fornecedor</option>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="submit" class="btn btn-primary">Cadastrar</button>
                <a href="index.php" class="btn btn-default">Cancelar</a>
            </td>
        </tr>
    </table>
</form>
</section>
<?php

?>


