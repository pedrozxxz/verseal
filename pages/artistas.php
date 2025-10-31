<?php
session_start();
require_once '../config/database.php';

$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;

// Buscar artistas do banco de dados
try {
    $stmt = $pdo->query("SELECT * FROM artistas WHERE ativo = 1 ORDER BY nome ASC");
    $artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback para dados estáticos em caso de erro
    $artistas = [
        [
            'id' => 1,
            'nome' => 'JAMILE FRANQUILIM',
            'idade' => 16,
            'descricao' => 'Artista de 16 anos que busca autonomia no mercado artístico, expondo seus desenhos manuais e digitais para Verseal.',
            'telefone' => '123456-7890',
            'email' => 'jamyfranquilim@gmail.com',
            'instagram' => '@oliveirzz.a',
            'cor_gradiente' => 'linear-gradient(135deg, #e07b67, #cc624e)',
            'icone' => 'fas fa-paint-brush',
            'imagem_perfil' => 'img/artistas/jamile.jpg'
        ],
        [
            'id' => 2,
            'nome' => 'STEFANI CORREA',
            'idade' => 17,
            'descricao' => 'Artista de 17 anos que busca autonomia no mercado artístico, realizando desenhos manuais para o site Verseal.',
            'telefone' => '33333-9999',
            'email' => 'tefa@gmail.com',
            'instagram' => '@stefani.correa',
            'cor_gradiente' => 'linear-gradient(135deg, #5b4a42, #8a7360)',
            'icone' => 'fas fa-pencil-alt',
            'imagem_perfil' => 'img/artistas/stefani.jpg'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Artistas - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/artista.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .btn-editar-perfil-card {
      display: inline-block;
      margin-top: 12px;
      background: linear-gradient(135deg, #cc624e, #b34f3e);
      color: #fff;
      padding: 8px 16px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.25s ease;
    }

    .btn-editar-perfil-card:hover {
      background: linear-gradient(135deg, #e07b67, #cc624e);
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(204,98,78,0.3);
    }

    /* Estilos para a imagem do artista */
    .artista-imagem {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px auto;
      overflow: hidden;
      border: 4px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      position: relative;
    }

    .artista-imagem img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }

    .artista-imagem .icone-fallback {
      font-size: 2.5rem;
      color: rgba(255, 255, 255, 0.9);
    }

    /* Animação fade-in */
    .fade-in {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .fade-in.show {
      opacity: 1;
      transform: translateY(0);
    }

    /* Estilo para quando não há artistas */
    .nenhum-artista {
      text-align: center;
      padding: 60px 20px;
      background: #f8f4f2;
      border-radius: 15px;
      grid-column: 1 / -1;
    }

    .nenhum-artista h3 {
      color: #666;
      margin-bottom: 10px;
    }

    .nenhum-artista p {
      color: #888;
    }

    /* Ajustes responsivos para as imagens */
    @media (max-width: 768px) {
      .artista-imagem {
        width: 100px;
        height: 100px;
      }
      
      .artista-imagem .icone-fallback {
        font-size: 2rem;
      }
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
    
    <!-- Dropdown Perfil -->
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
          <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
          <div class="dropdown-divider"></div>
          <a href="./pages/logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
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

  <!-- HERO ARTISTAS -->
  <section class="hero-artistas">
    <div class="hero-artistas-content">
      <h1>Nossos Artistas</h1>
      <p>Conheça os talentos por trás das obras que transformam.</p>
      <a href="#artistas" class="btn-destaque">Explorar Talentos</a>
    </div>
    <div class="hero-artistas-imagem">
      <div class="arte-abstrata"></div>
    </div>
  </section>

  <!-- SEÇÃO ARTISTAS -->
  <section class="artistas" id="artistas">
    <h2>Conheça Nossos Talentos</h2>
    <p class="artistas-subtitle">
      Artistas independentes que buscam autonomia no mercado artístico através da Verseal
    </p>
    
    <div class="galeria-artistas">
      <?php if (empty($artistas)): ?>
        <div class="nenhum-artista">
          <i class="fas fa-palette" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
          <h3>Nenhum artista cadastrado</h3>
          <p>Em breve teremos artistas incríveis para apresentar!</p>
        </div>
      <?php else: ?>
        <?php foreach ($artistas as $artista): ?>
        <div class="card-artista fade-in">
          <div class="artista-imagem" style="background: <?php echo $artista['cor_gradiente']; ?>;">
            <?php if (!empty($artista['imagem_perfil'])): ?>
              <img src="../<?php echo $artista['imagem_perfil']; ?>" 
                   alt="<?php echo htmlspecialchars($artista['nome']); ?>" 
                   onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
              <div class="icone-fallback" style="display: none;">
                <i class="<?php echo $artista['icone']; ?>"></i>
              </div>
            <?php else: ?>
              <div class="icone-fallback">
                <i class="<?php echo $artista['icone']; ?>"></i>
              </div>
            <?php endif; ?>
          </div>
          <div class="artista-info">
            <h3 class="artista-nome"><?php echo htmlspecialchars($artista['nome']); ?></h3>
            <p class="artista-idade"><?php echo $artista['idade']; ?> anos</p>
            <p class="artista-descricao">
              <?php echo htmlspecialchars($artista['descricao']); ?>
            </p>
            <div class="artista-contatos">
              <?php if (!empty($artista['telefone'])): ?>
              <div class="contato-item">
                <i class="fas fa-phone"></i>
                <span>Telefone: <?php echo htmlspecialchars($artista['telefone']); ?></span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($artista['email'])): ?>
              <div class="contato-item">
                <i class="fas fa-envelope"></i>
                <span>Email: <?php echo htmlspecialchars($artista['email']); ?></span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($artista['instagram'])): ?>
              <div class="contato-item">
                <i class="fab fa-instagram"></i>
                <span>Instagram: <?php echo htmlspecialchars($artista['instagram']); ?></span>
              </div>
              <?php endif; ?>
            </div>
            <?php if ($usuarioLogado === $artista['nome']): ?>
              <a href="editarbiografia.php?id=<?php echo $artista['id']; ?>" class="btn-editar-perfil-card">Editar Perfil</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- SOBRE ARTISTAS -->
  <section class="sobre-artistas">
    <div class="conteudo-sobre-artistas">
      <h2>O Poder da Expressão Artística</h2>
      <p>
        Na <strong>Verseal</strong>, acreditamos que cada artista tem uma voz única. Nossos talentos emergentes trazem
        <span class="destaque">autenticidade e paixão</span> para cada obra, criando conexões genuínas através da arte.
      </p>
      <p>
        Do desenho manual às criações digitais, cada peça conta uma história e carrega a essência de seu criador.
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
      const elementos = document.querySelectorAll('.card-artista');
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

    // Adicionar classe para animação
    document.querySelectorAll('.card-artista').forEach(card => {
      card.classList.add('fade-in');
    });

    // Verificar se as imagens carregam corretamente
    document.addEventListener('DOMContentLoaded', function() {
      const imagens = document.querySelectorAll('.artista-imagem img');
      imagens.forEach(img => {
        img.addEventListener('error', function() {
          this.style.display = 'none';
          const fallback = this.nextElementSibling;
          if (fallback && fallback.classList.contains('icone-fallback')) {
            fallback.style.display = 'flex';
          }
        });
      });
    });
  </script>
</body>
</html>