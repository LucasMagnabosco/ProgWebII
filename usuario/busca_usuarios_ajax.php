<?php

include_once '../fachada.php';

// procura usuários
$palavra = $_POST['pesquisa'] ?? '';
$tipo = $_POST['tipo'] ?? '';

$dao = $factory->getUsuarioDao();

$limit = '5';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
if($page > 1) {
    $start = (($page - 1) * $limit);
} else {
    $start = 0;
}


$usuarios = $dao->buscaFiltrada($palavra, $start, $limit);
$total_data = $dao->contaComNome($palavra);


$response = array(
    'usuarios' => $usuarios,
    'pagination' => '',
    'total' => $total_data
);


$total_links = ceil($total_data/$limit);
$previous_link = '';
$next_link = '';
$page_link = '';
$page_array = [];

if($total_links > 4) {
    if($page < 5) {
        // Mostra as primeiras 5 páginas
        for($count = 1; $count <= min(5, $total_links); $count++) {
            $page_array[] = $count;
        }
        if($total_links > 5) {
            $page_array[] = '...';
            $page_array[] = $total_links;
        }
    } else {
        $end_limit = $total_links - 5;
        if($page > $end_limit) {
            // Mostra as últimas 5 páginas
            $page_array[] = 1;
            $page_array[] = '...';
            for($count = max(1, $end_limit); $count <= $total_links; $count++) {
                $page_array[] = $count;
            }
        } else {
            // Mostra páginas ao redor da página atual
            $page_array[] = 1;
            $page_array[] = '...';
            for($count = max(1, $page - 1); $count <= min($page + 1, $total_links); $count++) {
                $page_array[] = $count;
            }
            $page_array[] = '...';
            $page_array[] = $total_links;
        }
    }
} else {
    // Se tiver 4 ou menos páginas, mostra todas
    for($count = 1; $count <= $total_links; $count++) {
        $page_array[] = $count;
    }
}

$pagination = '<nav aria-label="Navegação de páginas">
    <ul class="pagination justify-content-center">';

// Botão Anterior
if($page > 1) {
    $previous_link = '<li class="page-item">
        <a class="page-link" href="#" data-page_number="'.($page-1).'">
            <i class="fas fa-chevron-left"></i>
        </a>
    </li>';
}

// Links das páginas
for($count = 0; $count < count($page_array); $count++) {
    if($page_array[$count] === '...') {
        $page_link .= '<li class="page-item disabled">
            <a class="page-link" href="#">...</a>
        </li>';
    } else {
        $page_number = $page_array[$count];
        if($page == $page_number) {
            $page_link .= '<li class="page-item active">
                <a class="page-link" href="#">'.$page_number.'</a>
            </li>';
        } else {
            $page_link .= '<li class="page-item">
                <a class="page-link" href="#" data-page_number="'.$page_number.'">'.$page_number.'</a>
            </li>';
        }
    }
}

// Botão Próximo
if($page < $total_links) {
    $next_link = '<li class="page-item">
        <a class="page-link" href="#" data-page_number="'.($page+1).'">
            <i class="fas fa-chevron-right"></i>
        </a>
    </li>';
}

$pagination .= $previous_link . $page_link . $next_link;
$pagination .= '</ul></nav>';

$response['pagination'] = $pagination;


header('Content-Type: application/json');
echo json_encode($response);
?>
