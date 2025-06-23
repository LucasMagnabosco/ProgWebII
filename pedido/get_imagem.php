<?php
include_once '../fachada.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('ID inválido');
}

$id = (int)$_GET['id'];
$produtoDao = $factory->getProdutoDao();
$foto = $produtoDao->getFotoPorId($id);

if (!$foto) {
    // Lê imagem padrão e ajusta tipo
    $foto = file_get_contents('../assets/imagem-default.jpg');
    $mime = 'image/jpeg';
} else {
    if (is_resource($foto)) {
        $foto = stream_get_contents($foto);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($foto);

    if (!$mime) {
        $mime = 'image/jpeg';
    }
}

$base64 = base64_encode($foto);
$dataUrl = 'data:' . $mime . ';base64,' . $base64;

header('Content-Type: text/plain'); // Ou application/json se for incluir no JSON
echo $dataUrl;
exit;
?>