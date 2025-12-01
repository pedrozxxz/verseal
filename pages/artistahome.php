<?php
session_start();

// CONFIGURAÇÃO / BANCO
require_once 'config.php';
/*
  O config.php deve definir a variável:
  $conn = new mysqli(...);
*/

// Inicializações
$usuarioLogado = null;
$tipoUsuario = null;

// 🔹 Verificar Artista PRIMEIRO (pois queremos que artistas sejam identificados como artistas)
if (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}
// 🔹 Verificar Cliente DEPOIS (se não for artista)
elseif (isset($_SESSION["cliente"]) || isset($_SESSION["usuario"])) {
    $usuarioLogado = $_SESSION["cliente"] ?? $_SESSION["usuario"];
    $tipoUsuario = "cliente";
}

// 🔹 Verificar mensagens não lidas
$total_nao_lidas = 0;
if (isset($usuarioLogado['id']) && function_exists('getTotalMensagensNaoLidas')) {
    $total_nao_lidas = getTotalMensagensNaoLidas($conn, $usuarioLogado['id']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Área do Artista</title>

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/artistahome.css" />
</head>

<body>

<header>
    <div class="logo">Verseal</div>

    <nav>
      <a href="artistahome.php"><i class="fas fa-home"></i> Início</a>
      <a href="artistasobra.php"><i class="fas fa-palette"></i> Obras</a>
      <a href="artistabiografia.php"><i class="fas fa-user"></i> Quem eu sou?</a>

      <?php if (!empty($usuarioLogado['id'])): ?>
      <div class="notificacao-mensagens">
          <a href="artistaperfil.php?aba=mensagens" class="icon-link">
              <i class="fas fa-envelope"></i>
              
              <?php if ($total_nao_lidas > 0): ?>
                <span class="mensagens-badge" id="mensagensBadge"><?php echo $total_nao_lidas; ?></span>
              <?php endif; ?>
          </a>
      </div>
      <?php endif; ?>

      <!-- DROPDOWN PERFIL -->
      <div class="profile-dropdown">
          <a href="#" class="icon-link" id="profile-icon">
            <i class="fas fa-user"></i>
          </a>

          <div class="dropdown-content" id="profile-dropdown">
            <?php if (!empty($usuarioLogado['nome'])): ?>
              <div class="user-info">
                <p>Bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>!</p>
                <small><?php echo ucfirst($tipoUsuario); ?></small>
              </div>

              <div class="dropdown-divider"></div>
              <a href="./artistaperfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
              <a href="./editarbiografia.php" class="dropdown-item"><i class="fas fa-edit"></i> Editar Biografia</a>

              <div class="dropdown-divider"></div>
              <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>

            <?php else: ?>
              <div class="user-info"><p>Faça login para acessar seu perfil</p></div>
              <div class="dropdown-divider"></div>
              <a href="./login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
              <a href="./login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
            <?php endif; ?>
          </div>
      </div>

    </nav>
</header>

   <section class="hero">
    <div class="hero-content">
      <h1>ARTE QUE TRANSFORMA.</h1>
      <p>Crie obras únicas e as coloque em exibição aqui</p>
      <div class="hero-buttons">
        <a href="adicionarobra.php" class="btn-destaque">ADICIONAR OBRAS</a>
      </div>
    </div>

    <div class="hero-gallery">
      <img src="../img/imagem9.png" alt="Arte destaque 1" />
      <img src="../img/imagem.jfif" alt="Arte destaque 2" />
      <img src="../img/imagem2.png" alt="Arte destaque 3" />
    </div>
</section>


  <footer>
    <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
    <div class="social">
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-linkedin-in"></i></a>
      <a href="#"><i class="fab fa-whatsapp"></i></a>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>

<script>
  VANTA.WAVES({
    el: ".hero",
    mouseControls: true,
    touchControls: true,
    minHeight: 200.00,
    minWidth: 200.00,
    scale: 1.0,
    scaleMobile: 1.0,
    color: 0xfef9f6,
    shininess: 40.0,
    waveHeight: 20.0,
    waveSpeed: 0.5,
    zoom: 1
  });

  document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');

      profileIcon.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
      });

      document.addEventListener('click', function (e) {
        if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
          profileDropdown.style.display = 'none';
        }
      });

      profileDropdown.addEventListener('click', function (e) {
        e.stopPropagation();
      });

      // Badge mensagens
      function atualizarBadgeMensagens() {
          const badge = document.getElementById('mensagensBadge');
          const totalNaoLidas = <?php echo $total_nao_lidas; ?>;

          if (badge) {
              if (totalNaoLidas > 0) {
                  badge.textContent = totalNaoLidas;
                  badge.style.display = 'flex';
              } else {
                  badge.style.display = 'none';
              }
          }
      }

      atualizarBadgeMensagens();
  });
</script>

</body>
</html>