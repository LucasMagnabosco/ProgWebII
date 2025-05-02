<?php
include_once 'fachada.php';
include_once 'comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: /ProgWebII/login/login.php");
    exit();
}

$page_title = "Produtos";

// Busca todos os produtos cadastrados
$produtoDao = $factory->getProdutoDao();
$produtos = $produtoDao->buscaTodos();

// Adiciona o Font Awesome antes do layout_header
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
include_once 'layout_header.php';
?>

<div class="container mt-4">
    <!-- Barra de Pesquisa, Filtros e Botões -->
    <div class="row mb-4 align-items-center">
        <!-- Filtros -->
        <div class="col-md-3">
            <div class="filtro-section">
                <h5>Filtros</h5>
                <div class="mb-3">
                    <label class="form-label">Fornecedores</label>
                    <select class="form-select" id="filtroFornecedor" onchange="filtrarProdutos()">
                        <option value="">Todos</option>
                        <?php
                        $fornecedores = array_unique(array_column($produtos, 'fornecedor_nome'));
                        foreach ($fornecedores as $fornecedor) {
                            echo "<option value='" . htmlspecialchars($fornecedor) . "'>" . htmlspecialchars($fornecedor) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Barra de Pesquisa e Botões -->
        <div class="col-md-9">
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" id="pesquisa" class="form-control" placeholder="Buscar produtos..." onkeyup="filtrarProdutos()">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <div class="row">
        <div class="col-12">
            <div class="row row-cols-1 row-cols-md-3 g-4" id="listaProdutos">
                <?php foreach ($produtos as $produto): ?>
                    <div class="col">
                        <div class="card produto-card h-100">
                            <?php if (!empty($produto['foto'])): ?>
                                <img src="<?= htmlspecialchars($produto['foto']) ?>" class="card-img-top produto-imagem" alt="<?= htmlspecialchars($produto['nome']) ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x200?text=Sem+Imagem" class="card-img-top produto-imagem" alt="Sem imagem">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($produto['nome']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($produto['descricao']) ?></p>
                                <p class="card-text mt-auto">
                                    <small class="text-muted">
                                        Fornecedor: <?= htmlspecialchars($produto['fornecedor_nome']) ?>
                                    </small>
                                </p>
                                <button class="btn btn-primary w-100 mt-2">Ver Detalhes</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .produto-card {
        transition: transform 0.3s;
    }
    .produto-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .produto-imagem {
        height: 200px;
        object-fit: cover;
    }
    .filtro-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .highlight {
        background-color: yellow;
        font-weight: bold;
    }
    .card-body {
        display: flex;
        flex-direction: column;
    }
    .card-text {
        flex-grow: 1;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filtrarProdutos() {
        const filtro = document.getElementById('pesquisa').value.toLowerCase();
        const filtroFornecedor = document.getElementById('filtroFornecedor').value.toLowerCase();
        const cards = document.querySelectorAll('.produto-card');

        cards.forEach(card => {
            const nome = card.querySelector('.card-title').textContent.toLowerCase();
            const descricao = card.querySelector('.card-text').textContent.toLowerCase();
            const fornecedor = card.querySelector('.text-muted').textContent.toLowerCase();

            const matchFiltro = nome.includes(filtro) || descricao.includes(filtro);
            const matchFornecedor = !filtroFornecedor || fornecedor.includes(filtroFornecedor);

            if (matchFiltro && matchFornecedor) {
                card.closest('.col').style.display = '';
                
                // Destacar texto encontrado
                if (filtro) {
                    const nomeElement = card.querySelector('.card-title');
                    const descricaoElement = card.querySelector('.card-text');
                    
                    nomeElement.innerHTML = destacarTexto(nomeElement.textContent, filtro);
                    descricaoElement.innerHTML = destacarTexto(descricaoElement.textContent, filtro);
                }
            } else {
                card.closest('.col').style.display = 'none';
            }
        });
    }

    function destacarTexto(texto, filtro) {
        if (!filtro) return texto;
        const regex = new RegExp(`(${filtro})`, 'gi');
        return texto.replace(regex, '<span class="highlight">$1</span>');
    }
</script>
</body>
</html>
