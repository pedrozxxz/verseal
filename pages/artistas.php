<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;
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
          <a href="./minhas-compras.php" class="dropdown-item"><i class="fas fa-shopping-bag"></i> Minhas Compras</a>
          <a href="./favoritos.php" class="dropdown-item"><i class="fas fa-heart"></i> Favoritos</a>
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
          <a href="./pages/admhome.php" class="menu-item"><i class="fas fa-user-shield"></i> <span>ADM</span></a>
          <a href="./pages/artistahome.php" class="menu-item"><i class="fas fa-palette"></i> <span>Artista</span></a>
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

      <!-- Artista 1 -->
      <div class="card-artista">
        <div class="artista-imagem" style="background: linear-gradient(135deg, #e07b67, #cc624e);">
          <i class="fas fa-paint-brush"></i>
        </div>
        <div class="artista-info">
          <h3 class="artista-nome">JAMILE FRANQUILIM</h3>
          <p class="artista-idade">16 anos</p>
          <p class="artista-descricao">
            Artista de 16 anos que busca autonomia no mercado artístico, expondo seus desenhos manuais e digitais para Verseal.
          </p>
          <div class="artista-contatos">
            <div class="contato-item">
              <i class="fas fa-phone"></i>
              <span>Telefone: 123456-7890</span>
            </div>
            <div class="contato-item">
              <i class="fas fa-envelope"></i>
              <span>Email: jamyfranquilim@gmail.com</span>
            </div>
            <div class="contato-item">
              <i class="fab fa-instagram"></i>
              <span>Instagram: @oliveirzz.a</span>
            </div>
          </div>
          <?php if ($usuarioLogado === 'JAMILE FRANQUILIM'): ?>
            <a href="editarbiografia.php" class="btn-editar-perfil-card">Editar Perfil</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Artista 2 -->
      <div class="card-artista">
        <div class="artista-imagem" style="background: linear-gradient(135deg, #5b4a42, #8a7360);">
          <i class="fas fa-pencil-alt"></i>
        </div>
        <div class="artista-info">
          <h3 class="artista-nome">STEFAN CORREA</h3>
          <p class="artista-idade">17 anos</p>
          <p class="artista-descricao">
            Artista de 17 anos que busca autonomia no mercado artístico, realizando desenhos manuais para o site Verseal.
          </p>
          <div class="artista-contatos">
            <div class="contato-item">
              <i class="fas fa-phone"></i>
              <span>Telefone: 33333-9999</span>
            </div>
            <div class="contato-item">
              <i class="fas fa-envelope"></i>
              <span>Email: tefa@gmail.com</span>
            </div>
            <div class="contato-item">
              <i class="fab fa-instagram"></i>
              <span>Instagram: stefani@gmail.com</span>
            </div>
          </div>
          <?php if ($usuarioLogado === 'STEFAN CORREA'): ?>
            <a href="editarbiografia.php" class="btn-editar-perfil-card">Editar Perfil</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Artista 3 -->
      <div class="card-artista">
        <div class="artista-imagem" style="background: linear-gradient(135deg, #8a7360, #a88c7d);">
          <i class="fas fa-palette"></i>
        </div>
        <div class="artista-info">
          <h3 class="artista-nome">MARINA OLIVEIRA</h3>
          <p class="artista-idade">19 anos</p>
          <p class="artista-descricao">
            Artista especializada em ilustrações digitais e pinturas acrílicas, explorando temas de fantasia e surrealismo.
          </p>
          <div class="artista-contatos">
            <div class="contato-item">
              <i class="fas fa-phone"></i>
              <span>Telefone: 55555-7777</span>
            </div>
            <div class="contato-item">
              <i class="fas fa-envelope"></i>
              <span>Email: marina.arte@email.com</span>
            </div>
            <div class="contato-item">
              <i class="fab fa-instagram"></i>
              <span>Instagram: @marina.artes</span>
            </div>
          </div>
          <?php if ($usuarioLogado === 'MARINA OLIVEIRA'): ?>
            <a href="editarbiografia.php" class="btn-editar-perfil-card">Editar Perfil</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Artista 4 -->
      <div class="card-artista">
        <div class="artista-imagem" style="background: linear-gradient(135deg, #cc624e, #e07b67);">
          <i class="fas fa-pen-nib"></i>
        </div>
        <div class="artista-info">
          <h3 class="artista-nome">LUCAS FERNANDES</h3>
          <p class="artista-idade">20 anos</p>
          <p class="artista-descricao">
            Artista focado em retratos realistas e arte de rua, combinando técnicas tradicionais com elementos contemporâneos.
          </p>
          <div class="artista-contatos">
            <div class="contato-item">
              <i class="fas fa-phone"></i>
              <span>Telefone: 44444-8888</span>
            </div>
            <div class="contato-item">
              <i class="fas fa-envelope"></i>
              <span>Email: lucas.fernandes@email.com</span>
            </div>
            <div class="contato-item">
              <i class="fab fa-instagram"></i>
              <span>Instagram: @lucas_sketches</span>
            </div>
          </div>
          <?php if ($usuarioLogado === 'LUCAS FERNANDES'): ?>
            <a href="editarbiografia.php" class="btn-editar-perfil-card">Editar Perfil</a>
          <?php endif; ?>
        </div>
      </div>

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
</body>
</html>
