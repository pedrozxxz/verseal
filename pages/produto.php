<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Obras - Verseal</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

  <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="../index.php">Início</a>
      <a href="./produto.php">Obras</a>
      <a href="./sobre.php">Sobre</a>
      <a href="./artistas.php">Artistas</a>
      <a href="./contato.php">Contato</a>
      <a href="./minhas-compras.php" class="icon-link" id="cart-icon">
        <i class="fas fa-shopping-cart"></i>
      </a>
      <div class="profile-dropdown">
        <a href="#" class="icon-link" id="profile-icon">
          <i class="fas fa-user"></i>
        </a>
        <div class="dropdown-content" id="profile-dropdown">
          <?php if ($usuarioLogado): ?>
            <div class="user-info">
              <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado); ?></span>!</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./perfil.php" class="dropdown-item">
              <i class="fas fa-user-circle"></i> Meu Perfil
            </a>
            <a href="./minhas-compras.php" class="dropdown-item">
              <i class="fas fa-shopping-bag"></i> Minhas Compras
            </a>
            <a href="./favoritos.php" class="dropdown-item">
              <i class="fas fa-heart"></i> Favoritos
            </a>
            <div class="dropdown-divider"></div>
            <a href="./logout.php" class="dropdown-item logout-btn">
              <i class="fas fa-sign-out-alt"></i> Sair
            </a>
          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./login.php" class="dropdown-item">
              <i class="fas fa-sign-in-alt"></i> Fazer Login
            </a>
            <a href="./login.php" class="dropdown-item">
              <i class="fas fa-user-plus"></i> Cadastrar
            </a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>

  <section class="hero">
    <h1>Nossas Obras</h1>
    <p>Descubra peças únicas que carregam histórias e emoções.</p>
  </section>

  <section class="filtros">
    <input type="text" id="busca" placeholder="Buscar obra...">
    <select id="categoria">
      <option value="">Todas categorias</option>
      <option value="escultura">Esculturas</option>
      <option value="pintura">Pinturas</option>
      <option value="joia">Joias</option>
    </select>
    <select id="preco">
      <option value="">Todos preços</option>
      <option value="0-300">Até R$ 300</option>
      <option value="300-600">R$ 300 - R$ 600</option>
      <option value="600-1000">R$ 600 - R$ 1000</option>
    </select>
  </section>

  <section class="produtos-grid" id="produtosGrid">
    <!-- Produtos inseridos dinamicamente pelo JS -->
  </section>

  <div class="paginacao">
    <button id="carregarMais">Carregar mais</button>
  </div>

  <!-- RODAPÉ -->
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
          profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function (e) {
          if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
            profileDropdown.style.display = 'none';
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