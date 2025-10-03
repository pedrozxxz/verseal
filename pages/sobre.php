<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre - Verseal</title>
  <link rel="stylesheet" href="../css/sobre.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500&display=swap"
    rel="stylesheet">
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

  <section class="hero-sobre">
    <div id="particles-sobre"></div>
    <div class="hero-content">
      <h1>Sobre a Verseal</h1>
      <p>Transformando arte em experiências únicas, físicas e digitais.</p>
    </div>
  </section>

  <!-- QUEM SOMOS EM CARDS -->
  <section class="quem-somos container">
    <h2>Quem Somos</h2>
    <div class="cards-container">
      <div class="card-texto">
        <p>A Verseal nasceu da paixão por arte e inovação. Unimos técnicas tradicionais e digitais para criar peças únicas
          que contam histórias e provocam emoções, conectando artistas e colecionadores em uma experiência transformadora.</p>
      </div>
    </div>
  </section>

  <!-- MISSÃO EM CARDS -->
  <section class="missao container">
    <h2>Nossa Missão</h2>
    <div class="cards-missao">
      <div class="card">
        <i class="fas fa-palette icon-card"></i>
        <h3>Arte Autêntica</h3>
        <p>Valorizamos cada detalhe e a autenticidade de cada criação, preservando a essência única de cada artista.</p>
      </div>
      <div class="card">
        <i class="fas fa-laptop-code icon-card"></i>
        <h3>Inovação Digital</h3>
        <p>Unimos tecnologia e criatividade para criar experiências únicas que transcendem o convencional.</p>
      </div>
      <div class="card">
        <i class="fas fa-handshake icon-card"></i>
        <h3>Conexão</h3>
        <p>Proporcionar aos clientes uma relação verdadeira e significativa com a arte que adquirem.</p>
      </div>
    </div>
  </section>

  <!-- VALORES EM CARDS -->
  <section class="valores container">
    <h2>Nossos Valores</h2>
    <div class="cards-missao">
      <div class="card">
        <i class="fas fa-gem icon-card"></i>
        <h3>Excelência</h3>
        <p>Buscamos a perfeição em cada detalhe, desde a criação até a entrega final ao colecionador.</p>
      </div>
      <div class="card">
        <i class="fas fa-heart icon-card"></i>
        <h3>Paixão</h3>
        <p>Amamos o que fazemos e acreditamos no poder transformador da arte na vida das pessoas.</p>
      </div>
      <div class="card">
        <i class="fas fa-shield-alt icon-card"></i>
        <h3>Transparência</h3>
        <p>Mantemos relações claras e honestas com artistas, colecionadores e parceiros.</p>
      </div>
    </div>
  </section>

  <!-- PROJETO VERSEAL EM CARD -->
  <section class="projeto container">
    <h2>O Projeto Verseal</h2>
    <div class="cards-container">
      <div class="card-texto">
        <p>Nosso projeto busca unir o mundo físico e digital da arte de maneira harmoniosa. Cada obra é cuidadosamente criada, 
          podendo ser apreciada tanto como peça física única quanto como NFT exclusivo. Acreditamos em experiências imersivas 
          e personalizadas que conectam colecionadores e amantes da arte de forma profunda e significativa.</p>
        <p>Através da Verseal, democratizamos o acesso à arte de qualidade, permitindo que novos talentos sejam descobertos 
          e que colecionadores encontrem peças que realmente conversem com sua essência.</p>
      </div>
    </div>
  </section>

  <!-- VÍDEO PITCH -->
  <section class="video-pitch container">
    <h2>Conheça Nossa História</h2>
    <div class="video-container">
      <div class="card-video">
        <div class="video-wrapper">
          <iframe width="560" height="315" src="https://www.youtube.com/embed/20-niIkV-3M?si=XZy6feS-19UBfQMB" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
        <div class="video-info">
          <h3>Pitch Verseal</h3>
          <p>Assista ao nosso vídeo pitch e conheça mais sobre nossa missão, visão e os valores que nos movem.</p>
        </div>
      </div>
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

  <script src="https://cdn.jsdelivr.net/npm/three@0.150.1/build/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vanta/dist/vanta.waves.min.js"></script>
  <script>
    // Efeito Vanta Waves (opcional - descomente se quiser usar)
    /*
    VANTA.WAVES({
      el: "#particles-sobre",
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
    */

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