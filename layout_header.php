<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $page_title; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<style>
		body {
			padding-top: 100px;
		}
		.navbar {
			background-color: #f8f9fa;
			box-shadow: 0 2px 4px rgba(0,0,0,.1);
		}
		.navbar-brand {
			font-size: 1.5rem;
			font-weight: bold;
		}
		.nav-link {
			color: #6c757d;
		}
		.nav-link:hover {
			color: #0d6efd;
		}
		.cart-badge {
			position: absolute;
			top: -8px;
			right: -8px;
			padding: 0.25rem 0.5rem;
			font-size: 0.75rem;
			line-height: 1;
			border-radius: 50%;
			background-color: #dc3545;
			color: white;
		}
		.cart-button {
			position: relative;
		}
	</style>
</head>
<body class="bg-light">
	<nav class="navbar navbar-expand-lg navbar-light fixed-top">
		<div class="container-fluid">
			<a class="navbar-brand" href="/ProgWebII/visualiza_produtos.php"><?php echo $page_title; ?></a>
			<div class="ms-auto d-flex align-items-center">
				<?php
				include_once "comum.php";
				
				if (is_session_started() === FALSE) {
					session_start();
				}

				// Inicializa o carrinho se não existir
				if (!isset($_SESSION['carrinho'])) {
					$_SESSION['carrinho'] = [];
				}

				// Calcula o total de itens no carrinho
				$totalItens = array_sum(array_column($_SESSION['carrinho'], 'quantidade'));
				
				// Botão do carrinho
				echo '<a href="/ProgWebII/pedido/visualizar_carrinho.php" class="btn btn-outline-primary cart-button me-3">
					<i class="fas fa-shopping-cart"></i>
					' . ($totalItens > 0 ? '<span class="cart-badge">' . $totalItens . '</span>' : '') . '
				</a>';
				
				if(isset($_SESSION["usuario_id"])) {
					echo '<span class="me-3">Olá, ' . htmlspecialchars($_SESSION["usuario_nome"]) . '</span>';
					
					// Verifica se o usuário é admin
					$usuario = $factory->getUsuarioDao()->buscaPorId($_SESSION["usuario_id"]);
					if($usuario && $usuario->isAdmin()) {
						echo '<a href="/ProgWebII/usuario/permissoes.php" class="btn btn-info me-2">
							<i class="fas fa-user-shield"></i> Gerenciar Permissões
						</a>';
					}
					
					if(isset($_SESSION["is_fornecedor"]) && $_SESSION["is_fornecedor"]) {
						echo '<a href="/ProgWebII/produto/produtos.php" class="btn btn-warning me-2">
							<i class="fas fa-boxes"></i> Gerenciar Estoque
						</a>';
					}
					echo '<a href="/ProgWebII/login/executa_logout.php" class="btn btn-outline-danger me-2">Logout</a>';
				} else {
					echo '<a href="/ProgWebII/login/login.php" class="btn btn-outline-primary me-2">Login</a>';
				}

				// Verifica se não está na página de visualização de produtos
				$current_page = basename($_SERVER['PHP_SELF']);
				if ($current_page !== 'visualiza_produtos.php') {
					echo '<a href="/ProgWebII/visualiza_produtos.php" class="btn btn-outline-primary">
						<i class="fas fa-home"></i> Voltar aos Produtos
					</a>';
				}
				?>
			</div>
		</div>
	</nav>
</body>
</html>

