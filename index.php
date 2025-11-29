<?php
session_start();

// Verificar se usuário está logado (cliente ou artista)
$usuarioLogado = null;
$tipoUsuario = null;

// Verifica se há sessão de cliente
if (isset($_SESSION["clientes"]) && is_array($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
}
// Verifica se há sessão de artista
elseif (isset($_SESSION["artistas"]) && is_array($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}

// Inicializar carrinho de notificações se não existir
if (!isset($_SESSION['carrinho_notificacoes'])) {
    $_SESSION['carrinho_notificacoes'] = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Arte e NFT</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="./css/style.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .fade-in {
      opacity: 0;
      transform: translateY(40px);
      transition: opacity 0.8s ease-out, transform 0.4s ease-out;
    }
    .fade-in.show {
      opacity: 1;
      transform: translateY(0);
    }

    /* 🔹 SISTEMA DE NOTIFICAÇÕES */
    .notificacao-carrinho {
        position: relative;
        display: inline-block;
    }

    .carrinho-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        padding: 4px 8px;
        font-size: 0.7rem;
        min-width: 18px;
        height: 18px;
        text-align: center;
        line-height: 1;
        font-weight: bold;
        animation: pulse 2s infinite;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .badge-bounce {
        animation: bounce 0.5s ease;
    }

    @keyframes bounce {
        0%, 20%, 60%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        80% { transform: translateY(-5px); }
    }

    /* Dropdown styles */
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
      <a href="#">Início</a>
      <a href="pages/produto.php">Obras</a>
      <a href="pages/sobre.php">Sobre</a>
      <a href="pages/artistas.php">Artistas</a>
      <a href="pages/contato.php">Contato</a>
      
      <!-- 🔹 ÍCONE DO CARRINHO COM NOTIFICAÇÃO -->
      <div class="notificacao-carrinho">
          <a href="pages/carrinho.php" class="icon-link">
              <i class="fas fa-shopping-cart"></i>
              <span class="carrinho-badge" id="carrinhoBadge">
                  <?php 
                  $total_notificacoes = count($_SESSION['carrinho_notificacoes']);
                  if ($total_notificacoes > 0) {
                      echo $total_notificacoes;
                  }
                  ?>
              </span>
          </a>
      </div>
      
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
                    echo htmlspecialchars($usuarioLogado['nome']);
                  } elseif ($tipoUsuario === "artista") {
                    echo htmlspecialchars($usuarioLogado['nome_artistico']);
                  }
                  ?>
                </span>!
              </p>
            </div>
            <div class="dropdown-divider"></div>

            <?php if ($tipoUsuario === "cliente"): ?>
              <a href="pages/perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
            <?php elseif ($tipoUsuario === "artista"): ?>
              <a href="pages/artistahome.php" class="dropdown-item"><i class="fas fa-palette"></i> Meu Perfil</a>
            <?php endif; ?>

            <div class="dropdown-divider"></div>
            <a href="pages/logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>

          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="pages/login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
            <a href="pages/login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">
      <h1 class="fade-in">Arte que Transforma.</h1>
      <p class="fade-in">Explore NFTs e obras únicas feitas à mão.</p>
      <a href="./pages/produto.php" class="btn-destaque">Ver Obras</a>
    </div>
    <div class="hero-gallery fade-in">
      <img src="./img/imagem9.png" alt="Arte destaque 1" />
      <img src="./img/imagem.jfif" alt="Arte destaque 2" />
      <img src="./img/imagem2.png" alt="Arte destaque 3" />
    </div>
  </section>

  <!-- PRODUTOS -->
  <section id="produtos" class="produtos">
    <h2 class="fade-in">Obras em Destaque</h2>
    <div class="galeria">
      <div class="card fade-in">
        <img src="./img/midia.jfif" alt="Produto 1" />
        <h3>"Noite de Safira"</h3>
        <p>Arte digital - R$ 120</p>
      </div>
      <div class="card fade-in">
        <img src="./img/todos.jfif" alt="Produto 2" />
        <h3>"Princesa Das Sombras"</h3>
        <p>Arte Manual - R$ 200</p>
      </div>
      <div class="card fade-in">
        <img src="./img/desenho.jfif" alt="Produto 3" />
        <h3>"Guardiões Da Lâmina"</h3>
        <p>Arte NFT exclusiva - R$ 165</p>
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
  </script>

  <script>
    // 🔹 SISTEMA DE NOTIFICAÇÕES
    function atualizarBadgeCarrinho() {
        const badge = document.getElementById('carrinhoBadge');
        const totalNotificacoes = <?php echo count($_SESSION['carrinho_notificacoes']); ?>;
        
        if (totalNotificacoes > 0) {
            badge.textContent = totalNotificacoes;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function incrementarBadgeCarrinho() {
        const badge = document.getElementById('carrinhoBadge');
        let currentCount = parseInt(badge.textContent) || 0;
        currentCount++;
        
        badge.textContent = currentCount;
        badge.style.display = 'flex';
        
        // Animação de destaque
        badge.classList.add('badge-bounce');
        setTimeout(() => {
            badge.classList.remove('badge-bounce');
        }, 500);
    }

    // Atualizar badge quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        atualizarBadgeCarrinho();
    });
  </script>

  <script>
    // Animação ao rolar a página
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