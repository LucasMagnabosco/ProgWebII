<?php
$page_title = "Inserção de Usuário";
// layout do cabeçalho
include_once "layout_header.php";
 ?>
 <section>
<form action="insere_usuario.php" method="get">
    <table class='table table-hover table-responsive table-bordered'>
         <tr>
            <td>Email</td>
            <td><input type='email' name='email' class='form-control' /></td>
        </tr>
         <tr>
            <td>Senha</td>
            <td><input type='text' name='senha' class='form-control' /></td>
        </tr>
         <tr>
            <td>Nome</td>
            <td><input type='text' name='nome' class='form-control' /></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="submit" class="btn btn-primary">Inserir</button>
            </td>
        </tr>
    </table>
</form>
</section>
<?php

?>


