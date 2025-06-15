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
		/* Estilos do menu lateral */
		.drawer {
			position: fixed;
			top: 0;
			left: -300px;
			width: 300px;
			height: 100vh;
			background-color: #fff;
			box-shadow: 2px 0 5px rgba(0,0,0,0.1);
			transition: left 0.3s ease-in-out;
			z-index: 1050;
			padding: 20px;
		}
		.drawer.open {
			left: 0;
		}
		.drawer-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(0,0,0,0.5);
			display: none;
			z-index: 1040;
		}
		.drawer-overlay.open {
			display: block;
		}
		.drawer-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 1px solid #dee2e6;
		}
		.drawer-content {
			display: flex;
			flex-direction: column;
			gap: 10px;
		}
		.drawer-button {
			width: 100%;
			text-align: left;
			padding: 10px;
			border: none;
			background: none;
			color: #6c757d;
			transition: all 0.2s;
			position: relative;
		}
		.drawer-button:hover {
			background-color: #f8f9fa;
			color: #0d6efd;
		}
		.drawer-button i {
			margin-right: 10px;
			width: 20px;
			text-align: center;
		}
		.drawer-button .cart-badge {
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
		}
		.menu-toggle {
			background: none;
			border: none;
			font-size: 1.5rem;
			color: #6c757d;
			cursor: pointer;
			padding: 5px;
		}
		.menu-toggle:hover {
			color: #0d6efd;
		}
	</style>
</head>
<body class="bg-light">
	<nav class="navbar navbar-expand-lg navbar-light fixed-top">
		<div class="container-fluid">
			<div class="d-flex align-items-center">
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
				
				// Botão do menu
				echo '<button class="menu-toggle me-3" id="menuToggle">
					<i class="fas fa-bars"></i>
				</button>';
				?>
				<a class="navbar-brand" href="/ProgWebII/visualiza_produtos.php"><?php echo $page_title; ?></a>
			</div>
			<div class="ms-auto d-flex align-items-center">
				<?php
				// Botão home
				echo '<a href="/ProgWebII/visualiza_produtos.php" class="btn btn-outline-primary">
					<i class="fas fa-home"></i>
				</a>';
				?>
			</div>
		</div>
	</nav>

	<!-- Menu lateral -->
	<div class="drawer" id="drawer">
		<div class="drawer-header">
			<h5 class="mb-0">Menu</h5>
			<button class="menu-toggle" id="closeDrawer">
				<i class="fas fa-times"></i>
			</button>
		</div>
		<div class="drawer-content">
			<?php
			if(isset($_SESSION["usuario_id"])) {
				echo '<span class="drawer-button">
					<i class="fas fa-user"></i> Olá, ' . htmlspecialchars($_SESSION["usuario_nome"]) . '
				</span>';
				
				// Botão Minhas Compras
				echo '<a href="/ProgWebII/pedido/meus_pedidos.php" class="drawer-button">
					<i class="fas fa-shopping-bag"></i> Minhas Compras
				</a>';
				
				// Verifica se o usuário é admin
				$usuario = $factory->getUsuarioDao()->buscaPorId($_SESSION["usuario_id"]);
				if($usuario && $usuario->isAdmin()) {
					echo '<a href="/ProgWebII/usuario/permissoes.php" class="drawer-button">
						<i class="fas fa-user-shield"></i> Gerenciar Permissões
					</a>';
				}
				
				if(isset($_SESSION["is_fornecedor"]) && $_SESSION["is_fornecedor"]) {
					echo '<a href="/ProgWebII/produto/produtos.php" class="drawer-button">
						<i class="fas fa-boxes"></i> Gerenciar Estoque
					</a>';
				}
			}

			// Botão do carrinho
			echo '<a href="/ProgWebII/pedido/visualizar_carrinho.php" class="drawer-button">
				<i class="fas fa-shopping-cart"></i> Carrinho
				' . ($totalItens > 0 ? '<span class="cart-badge">' . $totalItens . '</span>' : '') . '
			</a>';

			if(isset($_SESSION["usuario_id"])) {
				echo '<a href="/ProgWebII/login/executa_logout.php" class="drawer-button">
					<i class="fas fa-sign-out-alt"></i> Logout
				</a>';
			} else {
				echo '<a href="/ProgWebII/login/login.php" class="drawer-button">
					<i class="fas fa-sign-in-alt"></i> Login
				</a>';
			}
			?>
		</div>
	</div>
	<div class="drawer-overlay" id="drawerOverlay"></div>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const menuToggle = document.getElementById('menuToggle');
		const closeDrawer = document.getElementById('closeDrawer');
		const drawer = document.getElementById('drawer');
		const overlay = document.getElementById('drawerOverlay');

		function toggleDrawer() {
			drawer.classList.toggle('open');
			overlay.classList.toggle('open');
		}

		menuToggle.addEventListener('click', toggleDrawer);
		closeDrawer.addEventListener('click', toggleDrawer);
		overlay.addEventListener('click', toggleDrawer);
	});
	</script>
</body>
</html>

