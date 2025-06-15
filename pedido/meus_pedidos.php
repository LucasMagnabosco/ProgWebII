<?php
include_once '../fachada.php';
include_once '../comum.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php?msg=Por favor, faça login para ver seus pedidos&tipo=warning");
    exit();
}

$page_title = "Meus Pedidos";
include_once '../layout_header.php';

// Busca os pedidos do usuário
$pedidoDao = $factory->getPedidoDao();
$pedidos = $pedidoDao->buscarPorCliente($_SESSION['usuario_id']);

// Função para formatar a data
function formatarData($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Função para formatar o status
function formatarStatus($status) {
    $statusClasses = [
        'PENDENTE' => 'warning',
        'APROVADO' => 'info',
        'EM_PREPARACAO' => 'primary',
        'ENVIADO' => 'info',
        'ENTREGUE' => 'success',
        'CANCELADO' => 'danger'
    ];
    
    $statusLabels = [
        'PENDENTE' => 'Pendente',
        'APROVADO' => 'Aprovado',
        'EM_PREPARACAO' => 'Em Preparação',
        'ENVIADO' => 'Enviado',
        'ENTREGUE' => 'Entregue',
        'CANCELADO' => 'Cancelado'
    ];
    
    $classe = $statusClasses[$status] ?? 'secondary';
    $label = $statusLabels[$status] ?? $status;
    
    return "<span class='badge bg-{$classe}'>{$label}</span>";
}
?>

<style>
.carousel-container {
    position: relative;
    width: 100%;
    height: 300px;
    overflow: hidden;
    margin: 10px 0;
    border-radius: 8px;
    background: #f8f9fa;
}

.carousel-slide {
    position: absolute;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: all 0.5s ease-in-out;
}

.carousel-slide img {
    transition: all 0.5s ease-in-out;
    object-fit: contain;
}

.carousel-slide img.main {
    max-width: 60%;
    max-height: 100%;
    z-index: 2;
}

.carousel-slide img.side {
    max-width: 30%;
    max-height: 80%;
    opacity: 0.6;
    transform: scale(0.8);
    z-index: 1;
}

.carousel-slide img.left {
    margin-right: -15%;
}

.carousel-slide img.right {
    margin-left: -15%;
}

.carousel-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    z-index: 10;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.carousel-button:hover {
    background: rgba(0, 0, 0, 0.8);
}

.carousel-prev {
    left: 10px;
}

.carousel-next {
    right: 10px;
}

.carousel-indicators {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 5px;
    z-index: 10;
}

.carousel-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
}

.carousel-indicator.active {
    background: white;
}

.single-product-image {
    width: 100%;
    height: 300px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f8f9fa;
    border-radius: 8px;
    margin: 10px 0;
}

.single-product-image img {
    max-width: 60%;
    max-height: 100%;
    object-fit: contain;
}

.slide-group {
    position: absolute;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: all 0.5s ease-in-out;
}
</style>

<div class="container mt-4">
    <h2 class="mb-4">Meus Pedidos</h2>
    
    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">
            Você ainda não fez nenhum pedido.
            <a href="../visualiza_produtos.php" class="alert-link">Clique aqui</a> para ver nossos produtos.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pedidos as $pedido): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pedido #<?php echo $pedido->getId(); ?></h5>
                            <?php echo formatarStatus($pedido->getStatus()); ?>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Data:</strong> <?php echo formatarData($pedido->getDataPedido()); ?><br>
                                <strong>Total:</strong> R$ <?php echo number_format($pedido->getTotal(), 2, ',', '.'); ?>
                            </p>
                            
                            <?php
                            $itens = $pedidoDao->buscarItensPedido($pedido->getId());
                            if (!empty($itens)):
                                if (count($itens) === 1):
                                    $item = $itens[0];
                                    $imagem = $item['produto_imagem'];
                                    $imagemBase64 = '';
                                    if ($imagem) {
                                        if (is_resource($imagem)) {
                                            $imagemBase64 = base64_encode(stream_get_contents($imagem));
                                        } else {
                                            $imagemBase64 = base64_encode($imagem);
                                        }
                                    }
                                    $src = $imagemBase64 ? "data:image/jpeg;base64," . $imagemBase64 : '../assets/imagem-default.jpg';
                            ?>
                                    <div class="single-product-image">
                                        <img src="<?php echo $src; ?>" 
                                             alt="<?php echo htmlspecialchars($item['produto_nome'] ?? ''); ?>">
                                    </div>
                            <?php else: ?>
                                    <div class="carousel-container" id="carousel-<?php echo $pedido->getId(); ?>">
                                        <div class="carousel-slide">
                                            <?php 
                                            $totalItens = count($itens);
                                            // Prepara todas as imagens primeiro
                                            $imagens = [];
                                            foreach ($itens as $item) {
                                                $imagem = $item['produto_imagem'];
                                                $imagemBase64 = '';
                                                if ($imagem) {
                                                    if (is_resource($imagem)) {
                                                        $imagemBase64 = base64_encode(stream_get_contents($imagem));
                                                    } else {
                                                        $imagemBase64 = base64_encode($imagem);
                                                    }
                                                }
                                                $imagens[] = [
                                                    'src' => $imagemBase64 ? "data:image/jpeg;base64," . $imagemBase64 : '../assets/imagem-default.jpg',
                                                    'nome' => $item['produto_nome']
                                                ];
                                            }
                                            
                                            // Gera os slides com as imagens já preparadas
                                            for ($i = 0; $i < $totalItens; $i++) {
                                                $prevIndex = ($i - 1 + $totalItens) % $totalItens;
                                                $nextIndex = ($i + 1) % $totalItens;
                                            ?>
                                                <div class="slide-group" style="display: <?php echo $i === 0 ? 'flex' : 'none'; ?>">
                                                    <img src="<?php echo $imagens[$prevIndex]['src']; ?>" 
                                                         alt="<?php echo htmlspecialchars($imagens[$prevIndex]['nome'] ?? ''); ?>"
                                                         class="side left"
                                                         onerror="this.src='../assets/imagem-default.jpg'">
                                                    <img src="<?php echo $imagens[$i]['src']; ?>" 
                                                         alt="<?php echo htmlspecialchars($imagens[$i]['nome'] ?? ''); ?>"
                                                         class="main"
                                                         onerror="this.src='../assets/imagem-default.jpg'">
                                                    <img src="<?php echo $imagens[$nextIndex]['src']; ?>" 
                                                         alt="<?php echo htmlspecialchars($imagens[$nextIndex]['nome'] ?? ''); ?>"
                                                         class="side right"
                                                         onerror="this.src='../assets/imagem-default.jpg'">
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <button class="carousel-button carousel-prev" onclick="prevSlide(<?php echo $pedido->getId(); ?>)">❮</button>
                                        <button class="carousel-button carousel-next" onclick="nextSlide(<?php echo $pedido->getId(); ?>)">❯</button>
                                        <div class="carousel-indicators">
                                            <?php foreach ($itens as $index => $item): ?>
                                                <div class="carousel-indicator <?php echo $index === 0 ? 'active' : ''; ?>"
                                                     onclick="goToSlide(<?php echo $pedido->getId(); ?>, <?php echo $index; ?>)"></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                            <?php endif; ?>
                            
                            <h6 class="mt-3">Itens do Pedido:</h6>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($itens as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($item['produto_nome']); ?>
                                        <span class="badge bg-secondary rounded-pill">
                                            <?php echo $item['quantidade']; ?> x R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <a href="pedido_sucesso.php?id=<?php echo $pedido->getId(); ?>" class="btn btn-primary btn-sm">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
const carousels = {};

function initCarousel(pedidoId, totalSlides) {
    carousels[pedidoId] = {
        currentSlide: 0,
        totalSlides: totalSlides
    };
}

function updateCarousel(pedidoId) {
    const carousel = carousels[pedidoId];
    const container = document.getElementById(`carousel-${pedidoId}`);
    const slideGroups = container.querySelectorAll('.slide-group');
    const indicators = container.querySelectorAll('.carousel-indicator');
    
    // Esconde todos os grupos de slides
    slideGroups.forEach(group => {
        group.style.display = 'none';
    });
    
    // Mostra o grupo de slides atual
    if (slideGroups[carousel.currentSlide]) {
        slideGroups[carousel.currentSlide].style.display = 'flex';
    }
    
    // Atualiza indicadores
    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === carousel.currentSlide);
    });
}

function nextSlide(pedidoId) {
    const carousel = carousels[pedidoId];
    carousel.currentSlide = (carousel.currentSlide + 1) % carousel.totalSlides;
    updateCarousel(pedidoId);
}

function prevSlide(pedidoId) {
    const carousel = carousels[pedidoId];
    carousel.currentSlide = (carousel.currentSlide - 1 + carousel.totalSlides) % carousel.totalSlides;
    updateCarousel(pedidoId);
}

function goToSlide(pedidoId, slideIndex) {
    const carousel = carousels[pedidoId];
    carousel.currentSlide = slideIndex;
    updateCarousel(pedidoId);
}

// Inicializa os carrosséis
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($pedidos as $pedido): ?>
        <?php
        $itens = $pedidoDao->buscarItensPedido($pedido->getId());
        if (!empty($itens) && count($itens) > 1):
        ?>
        initCarousel(<?php echo $pedido->getId(); ?>, <?php echo count($itens); ?>);
        <?php endif; ?>
    <?php endforeach; ?>
});
</script>

<?php include_once '../layout_footer.php'; ?> 