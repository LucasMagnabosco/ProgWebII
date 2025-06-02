<?php
include_once '../fachada.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit("ID não informado");
}

$dao = $factory->getProdutoDao();
$fotoBinaria = $dao->buscaFoto($id);

if (!$fotoBinaria) {
    http_response_code(404);
    exit("Imagem não encontrada");
}

// Logs para debug
error_log("ID do produto: " . $id);
error_log("Tamanho dos dados binários: " . strlen($fotoBinaria));
error_log("Primeiros 100 bytes em hex: " . bin2hex(substr($fotoBinaria, 0, 100)));

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($fotoBinaria);

error_log("Tipo MIME detectado: " . $mimeType);

// Limpa qualquer saída anterior
ob_clean();

// Define os headers
header("Content-Type: $mimeType");
header("Content-Length: " . strlen($fotoBinaria));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Envia os dados
echo $fotoBinaria;
exit;
