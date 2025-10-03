<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Arte e NFT</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="./css/style.css" />
</head>

<body>

  <!-- HEADER -->
  <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="#">Início</a>
      <a href="./pages/produto.php">Obras</a>
      <a href="./pages/sobre.php">Sobre</a>
      <a href="./pages/artistas.php">Artistas</a>
      <a href="./pages/contato.php">Contato</a>
      <a href="./pages/minhas-compras.php" class="icon-link" id="cart-icon">
        <i class="fas fa-shopping-cart"></i>
      </a>
      <div class="profile-dropdown">
        <a href="./pages/perfil.php" class="icon-link" id="profile-icon">
          <i class="fas fa-user"></i>
        </a>
        <div class="dropdown-content" id="profile-dropdown">
          <?php if ($usuarioLogado): ?>
            <div class="user-info">
              <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado); ?></span>!</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./pages/perfil.php" class="dropdown-item">
              <i class="fas fa-user-circle"></i> Meu Perfil
            </a>
            <a href="./pages/minhas-compras.php" class="dropdown-item">
              <i class="fas fa-shopping-bag"></i> Minhas Compras
            </a>
            <a href="./pages/favoritos.php" class="dropdown-item">
              <i class="fas fa-heart"></i> Favoritos
            </a>
            <div class="dropdown-divider"></div>
            <a href="./pages/logout.php" class="dropdown-item logout-btn">
              <i class="fas fa-sign-out-alt"></i> Sair
            </a>
          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./pages/login.php" class="dropdown-item">
              <i class="fas fa-sign-in-alt"></i> Fazer Login
            </a>
            <a href="./pages/login.php" class="dropdown-item">
              <i class="fas fa-user-plus"></i> Cadastrar
            </a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">
      <h1>Arte que Transforma.</h1>
      <p>Explore NFTs e obras únicas feitas à mão.</p>
      <a href="./pages/produto.php" class="btn-destaque">Ver Obras</a>
    </div>
    <div class="hero-gallery">
      <img src="./img/imagem9.png" alt="Arte destaque 1" />
      <img src="./img/imagem.jfif" alt="Arte destaque 2" />
      <img src="./img/imagem2.png" alt="Arte destaque 3" />
    </div>
  </section>

  <!-- PRODUTOS -->
  <section id="produtos" class="produtos">
    <h2>Obras em Destaque</h2>
    <div class="galeria">
      <div class="card">
        <img src="./img/midia.jfif" alt="Produto 1" />
        <h3>"Noite de Safira"</h3>
        <p>Arte digital - R$ 120</p>
      </div>
      <div class="card">
        <img src="./img/todos.jfif" alt="Produto 2" />
        <h3>"Princesa Das Sombras"</h3>
        <p>Arte Manual - R$ 200</p>
      </div>
      <div class="card">
        <img src="./img/desenho.jfif" alt="Produto 3" />
        <h3>"Guardiões Da Lâmina"</h3>
        <p>Arte NFT exclusiva - R$ 165</p>
      </div>
    </div>
  </section>

  <!-- SOBRE COM PARTÍCULAS -->
  <section class="sobre" id="sobre">
    <div id="particles-sobre"></div>
    <div class="conteudo-sobre">
      <h2>Sobre a Verseal</h2>
      <p>
        Unindo o digital ao artesanal, a <strong>Verseal</strong> é o elo entre o <em>futuro</em> e o <em>feito à
          mão</em>. Aqui, artistas expressam sua alma em NFTs e criações únicas, para colecionadores que buscam mais do
        que uma obra: uma <span class="destaque">conexão real</span>.
      </p>
      <p>
        Somos mais do que um marketplace. Somos uma galeria viva de expressão, movimento e autenticidade.
      </p>
    </div>
  </section>

    <!-- RODAPÉ -->
  <footer>
    <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
    <div class="social">
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-linkedin-in"></i></a>
      <a href="#"><i class="fab fa-whatsapp"></i></a>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
  <script>
    // Efeito Vanta Waves
    VANTA.WAVES({
      el: "#sobre",
      mouseControls: true,
      touchControls: true,
      minHeight: 200.00,
      minWidth: 200.00,
      scale: 1.0,
      scaleMobile: 1.0,
      color: 0x8a7360,
      shininess: 40.0,
      waveHeight: 20.0,
      waveSpeed: 0.5,
      zoom: 1
    });

    // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');

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
    });
  </script>
</body>

</html>