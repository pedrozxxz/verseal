<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;
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
    }
    
    .btn-voltar:hover {
      background: #e07b67;
    }
  </style>
</head>
<body>

  <!-- HEADER (mesmo do carrinho) -->
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
        <a href="./perfil.php" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
        <div class="dropdown-content" id="profile-dropdown">
          <?php if ($usuarioLogado): ?>
            <div class="user-info">
              <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado); ?></span>!</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
            <a href="./minhas-compras.php" class="dropdown-item"><i class="fas fa-shopping-bag"></i> Minhas Compras</a>
            <a href="./favoritos.php" class="dropdown-item"><i class="fas fa-heart"></i> Favoritos</a>
            <div class="dropdown-divider"></div>
            <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
            <a href="./login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
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
</body>
</html>