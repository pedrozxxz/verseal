<?php
session_start();

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se há um pedido na sessão
if (!isset($_SESSION['ultimo_pedido'])) {
    // Se não há pedido, redirecionar para a página inicial
    header('Location: ../index.php');
    exit;
}

$pedido = $_SESSION['ultimo_pedido'];
$usuarioLogado = null;

// Verificar sessão do usuário de forma mais abrangente
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
} elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
} elseif (isset($_SESSION["usuario"])) {
    $usuarioLogado = $_SESSION["usuario"];
    $tipoUsuario = "usuario";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verseal - Compra Realizada</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .pagina-sucesso {
      padding: 80px 7%;
      text-align: center;
      min-height: 70vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    
    .icone-sucesso {
      font-size: 4rem;
      color: #28a745;
      margin-bottom: 20px;
    }
    
    .pagina-sucesso h1 {
      font-family: 'Playfair Display', serif;
      color: #28a745;
      margin-bottom: 20px;
    }
    
    .pagina-sucesso p {
      font-size: 1.1rem;
      color: #666;
      margin-bottom: 30px;
      max-width: 500px;
    }
    
    .btn-voltar {
      background: #cc624e;
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      display: inline-block;
      transition: background 0.3s;
    }
    
    .btn-voltar:hover {
      background: #e07b67;
    }

    .info-pedido {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin: 20px 0;
      text-align: left;
      max-width: 400px;
    }

    .info-pedido h3 {
      color: #333;
      margin-bottom: 15px;
    }

    .info-pedido p {
      margin: 8px 0;
      color: #555;
    }

    /* Estilos do dropdown */
    .profile-dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background: white;
      min-width: 220px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      border-radius: 8px;
      z-index: 1000;
      padding: 10px 0;
    }

    .dropdown-content.show {
      display: block;
    }

    .user-info {
      padding: 10px 15px;
      border-bottom: 1px solid #eee;
    }

    .user-info p {
      margin: 0;
      font-size: 0.9rem;
      color: #333;
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      padding: 8px 15px;
      text-decoration: none;
      color: #333;
      transition: background 0.3s;
    }

    .dropdown-item:hover {
      background: #f8f9fa;
    }

    .dropdown-item i {
      margin-right: 10px;
      width: 16px;
      text-align: center;
    }

    .dropdown-divider {
      height: 1px;
      background: #eee;
      margin: 5px 0;
    }

    .logout-btn {
      color: #dc3545;
    }

    .logout-btn:hover {
      background: #f8d7da;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="../index.php">Início</a>
      <a href="./produto.php">Obras</a>
      <a href="./sobre.php">Sobre</a>
      <a href="./artistas.php">Artistas</a>
      <a href="./contato.php">Contato</a>
      <a href="./carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
      
      <div class="profile-dropdown">
        <a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
        <div class="dropdown-content" id="profile-dropdown">
          <?php if ($usuarioLogado): ?>
            <div class="user-info">
              <p>
                Seja bem-vindo, 
                <span id="user-name">
                  <?php 
                  if (is_array($usuarioLogado)) {
                    echo htmlspecialchars($usuarioLogado['nome'] ?? $usuarioLogado['nome_artistico'] ?? 'Usuário');
                  } else {
                    echo htmlspecialchars($usuarioLogado);
                  }
                  ?>
                </span>!
              </p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
            <div class="dropdown-divider"></div>
            <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
            <a href="./cadastro.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>

  <!-- CONTEÚDO -->
  <main class="pagina-sucesso">
    <div class="icone-sucesso">
      <i class="fas fa-check-circle"></i>
    </div>
    <h1>Compra Realizada com Sucesso!</h1>
    
    <div class="info-pedido">
      <h3>Resumo do Pedido</h3>
      <p><strong>Número do Pedido:</strong> <?php echo htmlspecialchars($pedido['id'] ?? 'N/A'); ?></p>
      <p><strong>Valor Total:</strong> R$ <?php echo number_format($pedido['valor'] ?? 0, 2, ',', '.'); ?></p>
      <p><strong>Método de Pagamento:</strong> <?php echo htmlspecialchars($pedido['metodo'] ?? 'PIX'); ?></p>
      <p><strong>Status:</strong> <?php echo htmlspecialchars($pedido['status'] ?? 'Confirmado'); ?></p>
    </div>
    
    <p>Obrigado por sua compra. Você receberá um email de confirmação em breve com os detalhes do seu pedido.</p>
    <a href="../index.php" class="btn-voltar">Voltar à Página Inicial</a>
  </main>

  <!-- FOOTER -->
  <footer>
    <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
    <div class="social">
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-linkedin-in"></i></a>
      <a href="#"><i class="fab fa-whatsapp"></i></a>
    </div>
  </footer>

  <script>
    // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');
      
      if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          profileDropdown.classList.toggle('show');
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function (e) {
          if (!profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
          }
        });

        // Prevenir fechamento ao clicar dentro do dropdown
        profileDropdown.addEventListener('click', function (e) {
          e.stopPropagation();
        });
      }
    });
  </script>
</body>
</html>