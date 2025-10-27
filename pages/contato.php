<?php
session_start();

$usuarioLogado = $_SESSION['usuario'] ?? null;
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nome = trim($_POST['nome']);
  $email = trim($_POST['email']);
  $mensagemContato = trim($_POST['mensagem']);

  if (empty($nome) || empty($email) || empty($mensagemContato)) {
    $mensagem = "<p class='erro'>Por favor, preencha todos os campos.</p>";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $mensagem = "<p class='erro'>Digite um e-mail válido.</p>";
  } else {
    $mensagem = "<p class='sucesso'>Obrigado por entrar em contato, <strong>$nome</strong>! Responderemos em breve.</p>";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contato - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/contato.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    
    <!-- Dropdown Perfil -->
    <div class="profile-dropdown">
      <a href="perfil.php" class="icon-link" id="profile-icon">
        <i class="fas fa-user"></i>
      </a>
      <div class="dropdown-content" id="profile-dropdown">
        <?php if ($usuarioLogado): ?>
          <div class="user-info">
            <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado); ?></span>!</p>
          </div>
          <div class="dropdown-divider"></div>
          <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
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

    <!-- Menu Hamburguer Flutuante -->
    <div class="hamburger-menu-desktop">
      <input type="checkbox" id="menu-toggle-desktop">
      <label for="menu-toggle-desktop" class="hamburger-desktop">
        <i class="fas fa-bars"></i>
        <span>ACESSO</span>
      </label>
      <div class="menu-content-desktop">
        <div class="menu-section">
          <a href="../index.php" class="menu-item" onclick="document.getElementById('menu-toggle-desktop').checked = false;">
            <i class="fas fa-user"></i> <span>Cliente</span>
          </a>
          <a href="./admhome.php" class="menu-item"><i class="fas fa-user-shield"></i> <span>ADM</span></a>
          <a href="./artistahome.php" class="menu-item"><i class="fas fa-palette"></i> <span>Artista</span></a>
        </div>
      </div>
    </div>
  </nav>
</header>

  <main>
    <div class="container">
      <h1>Fale Conosco</h1>
      <?php echo $mensagem; ?>
      <form method="post" action="">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" placeholder="Digite seu nome" required>

        <label for="email">E-mail</label>
        <input type="email" name="email" id="email" placeholder="Digite seu e-mail" required>

        <label for="mensagem">Mensagem</label>
        <textarea name="mensagem" id="mensagem" placeholder="Escreva sua mensagem..." required></textarea>

        <button type="submit">Enviar Mensagem</button>
      </form>
      <p class="voltar"><a href="../index.php">⬅ Voltar ao início</a></p>
    </div>
  </main>

  <script>
    document.addEventListener("mousemove", e => {
      let x = (e.clientX / window.innerWidth - 0.5) * 15;
      let y = (e.clientY / window.innerHeight - 0.5) * 15;
      document.querySelectorAll(".layer").forEach((layer, i) => {
        let depth = (i - 1) * 20;
        layer.style.transform = `rotateY(${x}deg) rotateX(${-y}deg) translateZ(${depth}px)`;
      });
    });
  </script>
  <script>
    // Espera o DOM carregar
    document.addEventListener('DOMContentLoaded', () => {
      const mensagem = document.querySelector('.sucesso, .erro');
      if (mensagem) {
        setTimeout(() => {
          mensagem.style.transition = 'opacity 0.8s ease';
          mensagem.style.opacity = '0';
          setTimeout(() => mensagem.remove(), 800);
        }, 4000);
      }
    });

  <script src="https://cdn.jsdelivr.net/npm/three@0.150.1/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta/dist/vanta.waves.min.js"></script>
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
      document.addEventListener('click', function (e) {
        if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
          profileDropdown.style.display = 'none';
        }
      });
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