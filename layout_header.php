<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $page_title; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
	</style>
</head>
<body class="bg-light">
	<nav class="navbar navbar-expand-lg navbar-light fixed-top">
		<div class="container-fluid">
			<a class="navbar-brand" href="index.php"><?php echo $page_title; ?></a>
			<div class="ms-auto">
				<?php
				include_once "comum.php";
				
				if (is_session_started() === FALSE) {
					session_start();
				}
				
				if(isset($_SESSION["usuario_id"])) {
					echo '<span class="me-3">Olá, ' . htmlspecialchars($_SESSION["usuario_nome"]) . '</span>';
					echo '<a href="executa_logout.php" class="btn btn-outline-danger">Logout</a>';
				} else {
					echo '<a href="login.php" class="btn btn-outline-primary">Login</a>';
				}
				?>
			</div>
		</div>
	</nav>
</body>
</html>

