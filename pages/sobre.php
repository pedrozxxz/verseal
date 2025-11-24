<?php
session_start();

// Verificar se usuário está logado (cliente ou artista)
$usuarioLogado = null;
$tipoUsuario = null;

// Verifica se há sessão de cliente (corrigido para "clientes" no plural)
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
}
// Verifica se há sessão de artista (corrigido para "artistas" no plural)
elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/sobre.css">
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .icon-card{
      align-self: center;
    }
    .fade-in {
      opacity: 0;
      transform: translateY(40px);
      transition: opacity 0.8s ease-out, transform 0.4s ease-out;
    }
    .fade-in.show {
      opacity: 1;
      transform: translateY(0);
    }
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
<header>
  <div class="logo">Verseal</div>
  <nav>
    <a href="../index.php">Início</a>
    <a href="./produto.php">Obras</a>
    <a href="./sobre.php">Sobre</a>
    <a href="./artistas.php">Artistas</a>
    <a href="./contato.php">Contato</a>
    
    <a href="./carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
    
    <!-- Dropdown Perfil CORRIGIDO -->
    <div class="profile-dropdown">
      <a href="#" class="icon-link" id="profile-icon">
        <i class="fas fa-user"></i>
      </a>
      <div class="dropdown-content" id="profile-dropdown">
        <?php if ($usuarioLogado): ?>
          <div class="user-info">
            <p>
              Seja bem-vindo, 
              <span id="user-name">
                <?php 
                if ($tipoUsuario === "cliente") {
                  // Verifica a estrutura do array de cliente
                  echo htmlspecialchars(is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado);
                } elseif ($tipoUsuario === "artista") {
                  // Verifica a estrutura do array de artista
                  echo htmlspecialchars(is_array($usuarioLogado) ? ($usuarioLogado['nome_artistico'] ?? $usuarioLogado['nome']) : $usuarioLogado);
                }
                ?>
              </span>!
            </p>
          </div>
          <div class="dropdown-divider"></div>

          <?php if ($tipoUsuario === "cliente"): ?>
            <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
          <?php elseif ($tipoUsuario === "artista"): ?>
            <a href="./artistahome.php" class="dropdown-item"><i class="fas fa-palette"></i> Meu Perfil</a>
          <?php endif; ?>

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

<main>
  <!-- HERO -->
  <section class="hero-sobre">
    <div id="particles-sobre"></div>
    <div class="hero-content">
      <h1 class="fade-in">Sobre a Verseal</h1>
      <p class="fade-in">Transformando arte em experiências únicas, físicas e digitais.</p>
    </div>
  </section>

  <!-- QUEM SOMOS -->
  <section class="quem-somos container fade-in">
    <h2 class="fade-in">Quem Somos</h2>
    <div class="cards-container">
      <div class="card-texto">
        <p class="fade-in">A Verseal nasceu da paixão por arte e inovação. Unimos técnicas tradicionais e digitais para criar peças únicas que contam histórias e provocam emoções, conectando artistas e colecionadores em uma experiência transformadora.</p>
      </div>
    </div>
  </section>

  <!-- MISSÃO -->
  <section class="missao container fade-in">
    <h2 class="fade-in">Nossa Missão</h2>
    <div class="cards-missao">
      <div class="card fade-in">
        <i class="fas fa-palette icon-card"></i>
        <h3 class="fade-in">Arte Autêntica</h3>
        <p class="fade-in">Valorizamos cada detalhe e a autenticidade de cada criação, preservando a essência única de cada artista.</p>
      </div>
      <div class="card fade-in">
        <i class="fas fa-laptop-code icon-card"></i>
        <h3 class="fade-in">Inovação Digital</h3>
        <p class="fade-in">Unimos tecnologia e criatividade para criar experiências únicas que transcendem o convencional.</p>
      </div>
      <div class="card fade-in">
        <i class="fas fa-handshake icon-card"></i>
        <h3 class="fade-in">Conexão</h3>
        <p class="fade-in">Proporcionar aos clientes uma relação verdadeira e significativa com a arte que adquirem.</p>
      </div>
    </div>
  </section>

  <!-- VALORES -->
  <section class="valores container fade-in">
    <h2 class="fade-in">Nossos Valores</h2>
    <div class="cards-missao fade-in">
      <div class="card fade-in">
        <i class="fas fa-gem icon-card"></i>
        <h3 class="fade-in">Excelência</h3>
        <p class="fade-in">Buscamos a perfeição em cada detalhe, desde a criação até a entrega final ao colecionador.</p>
      </div>
      <div class="card fade-in">
        <i class="fas fa-heart icon-card"></i>
        <h3 class="fade-in">Paixão</h3>
        <p class="fade-in">Amamos o que fazemos e acreditamos no poder transformador da arte na vida das pessoas.</p>
      </div>
      <div class="card fade-in">
        <i class="fas fa-shield-alt icon-card"></i>
        <h3 class="fade-in">Transparência</h3>
        <p class="fade-in">Mantemos relações claras e honestas com artistas, colecionadores e parceiros.</p>
      </div>
    </div>
  </section>

  <!-- PROJETO VERSEAL -->
  <section class="projeto container fade-in">
    <h2>O Projeto Verseal</h2>
    <div class="cards-container">
      <div class="card-texto">
        <p>Nosso projeto busca unir o mundo físico e digital da arte de maneira harmoniosa. Cada obra é cuidadosamente criada, podendo ser apreciada tanto como peça física única quanto como NFT exclusivo. Acreditamos em experiências imersivas e personalizadas que conectam colecionadores e amantes da arte de forma profunda e significativa.</p>
        <p>Através da Verseal, democratizamos o acesso à arte de qualidade, permitindo que novos talentos sejam descobertos e que colecionadores encontrem peças que realmente conversem com sua essência.</p>
      </div>
    </div>
  </section>

  <!-- VÍDEO PITCH -->
  <section class="video-pitch container fade-in">
    <h2>Conheça Nossa História</h2>
    <div class="video-container">
      <div class="card-video">
        <div class="video-wrapper">
          <iframe width="560" height="315" src="https://www.youtube.com/embed/20-niIkV-3M?si=XZy6feS-19UBfQMB" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        </div>
        <div class="video-info">
          <h3>Pitch Verseal</h3>
          <p>Assista ao nosso vídeo pitch e conheça mais sobre nossa missão, visão e os valores que nos movem.</p>
        </div>
      </div>
    </div>
  </section>
</main>

<footer>
  <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
  <div class="social">
    <a href="#"><i class="fab fa-instagram"></i></a>
    <a href="#"><i class="fab fa-linkedin-in"></i></a>
    <a href="#"><i class="fab fa-whatsapp"></i></a>
  </div>
</footer>

<script>
  // Dropdown do perfil CORRIGIDO
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

  // Fade-in on scroll
  document.addEventListener('DOMContentLoaded', () => {
    const elementos = document.querySelectorAll('.fade-in');
    const observador = new IntersectionObserver((entradas) => {
      entradas.forEach(entrada => {
        if (entrada.isIntersecting) {
          entrada.target.classList.add('show');
          observador.unobserve(entrada.target);
        }
      });
    }, { threshold: 0.2 });
    elementos.forEach(el => observador.observe(el));
  });
</script>
</body>
</html>