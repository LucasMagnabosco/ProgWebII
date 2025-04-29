<?php
session_start();


if (!isset($_SESSION['usuario_id'])) {
    header("Location: novo_usuario.php");
    exit;
}

$page_title = "Cadastro de Endereço";
include_once "layout_header.php";
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Cadastro de Endereço</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-<?php echo $_GET['tipo']; ?>">
                            <?php echo htmlspecialchars($_GET['msg']); ?></div>
                    <?php endif; ?>
                    
                    <form action="insere_endereco.php" method="post">
                        <div class="mb-3">
                            <label for="rua" class="form-label">Rua</label>
                            <input type="text" id="rua" name="rua" class="form-control" required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" id="numero" name="numero" class="form-control" required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" id="complemento" name="complemento" class="form-control" />
                        </div>
                        
                        <div class="mb-3">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" id="bairro" name="bairro" class="form-control" required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" id="cidade" name="cidade" class="form-control" required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-control" required>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" id="cep" name="cep" class="form-control" required />
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Cadastrar Endereço</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <a href="login.php" class="btn btn-link">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once "layout_footer.php";
?> 