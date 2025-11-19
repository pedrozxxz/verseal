<?php
require_once 'config.php';

// Verificar se está logado (como artista ou usuário)
if (!isArtista() && !isUsuario()) {
    header("Location: login.php");
    exit();
}
// Buscar dados do artista usando nossa função unificada
$artista = getUsuarioLogado($conn);

// Se não encontrou o artista no banco, usar dados padrão
if (!$artista) {
    $artista = [
        "nome" => $_SESSION["artistas"]['nome'] ?? "Artista",
        "descricao" => "Artista que busca autonomia no mercado artístico, expondo seus desenhos manuais e digitais para Verseal.",
        "telefone" => "",
        "email" => "",
        "instagram" => "",
        "imagem_perfil" => "../img/jamile.jpg"
    ];
}

// Fechar conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verseal - Biografia do Artista</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/produto.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    section {
      max-width: 900px;
      margin: 100px auto 40px;
      background: #fff;
      border-radius: 25px;
      padding: 60px 50px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 60px;
      position: relative;
      font-family: 'Open Sans', sans-serif;
    }

    section img {
      width: 500px;
      height: 400px;
      object-fit: cover;
      border-radius: 20px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .bio-texto h1 {
      position: absolute;
      top: -90px;
      left: 50%;
      transform: translateX(-50%);
      font-family: 'Playfair Display', serif;
      font-size: 2.5rem;
      font-weight: 700;
      color: #e07b67;
      padding: 8px 40px;
      border-radius: 30px;
      text-transform: uppercase;
      letter-spacing: 3px;
    }

    .bio-texto h2 {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      color: #333;
      margin-bottom: 15px;
    }

    .bio-texto h3 {
      font-family: 'Playfair Display', serif;
      color: #e07b67;
      margin-top: 25px;
      font-size: 1.4rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .bio-texto p {
      font-size: 1rem;
      color: #555;
      margin-bottom: 12px;
      line-height: 1.6;
    }

    .btn-editar {
      display: inline-block;
      margin: 10px 5px 0 0;
      padding: 8px 18px;
      background: linear-gradient(135deg, #e07b67, #cc624e);
      color: #fff;
      border: none;
      border-radius: 20px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-editar:hover {
      transform: translateY(-2px);
      background: linear-gradient(135deg, #cc624e, #e07b67);
      box-shadow: 0 6px 15px rgba(224, 123, 103, 0.4);
    }

    .bio-item {
      margin-bottom: 15px;
    }

    .btn-container {
      margin-top: 20px;
      text-align: center;
    }

    .btn-completo {
      display: inline-block;
      padding: 12px 30px;
      background: linear-gradient(135deg, #e07b67, #cc624e);
      color: #fff;
      border: none;
      border-radius: 25px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 6px 15px rgba(224, 123, 103, 0.3);
    }

    .btn-completo:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(224, 123, 103, 0.4);
    }

    @media (max-width: 768px) {
      section {
        flex-direction: column;
        padding: 40px 25px;
        gap: 30px;
      }
      
      section img {
        width: 100%;
        height: 300px;
      }
    }
  </style>
</head>

<body>
 <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="artistahome.php"><i class="fas fa-home"></i> Início</a>
      <a href="artistasobra.php"><i class="fas fa-palette"></i> Obras</a>
      <a href="artistabiografia.php"><i class="fas fa-user"></i> Quem eu sou?</a>
    
    <div class="hamburger-menu-desktop">
      <input type="checkbox" id="menu-toggle-desktop">
      <label for="menu-toggle-desktop" class="hamburger-desktop"><i class="fas fa-bars"></i><span>ACESSO</span></label>
      <div class="menu-content-desktop">
        <div class="menu-section">
          <a href="../index.php" class="menu-item"><i class="fas fa-user"></i><span>Cliente</span></a>
          <a href="./admhome.php" class="menu-item"><i class="fas fa-user-shield"></i><span>ADM</span></a>
          <a href="./artistahome.php" class="menu-item"><i class="fas fa-palette"></i><span>Artista</span></a>
        </div>
      </div>
    </div>

    <div class="profile-dropdown">
      <a href="./artistaperfil.php" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
      <div class="dropdown-content" id="profile-dropdown">
          <div class="user-info">
            <p>Seja bem-vindo, <?php echo htmlspecialchars($artista['nome'] ?? 'Artista'); ?>!</p>
            <small>Artista</small>
          </div>
          <div class="dropdown-divider"></div>
          <a href="./artistaperfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
          <a href="./editarbiografia.php" class="dropdown-item"><i class="fas fa-edit"></i> Editar Biografia</a>
          <div class="dropdown-divider"></div>
          <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
      </div>
    </div>
  </nav>
</header>

  <!-- SEÇÃO BIOGRAFIA -->
  <section>
<img src="<?php echo getImagemComTimestamp($artista['imagem_perfil'] ?? ''); ?>" 
     alt="<?php echo htmlspecialchars($artista['nome']); ?>"
     id="foto-perfil-artista">
    <div class="bio-texto">
      <h1>SOBRE</h1>
      <h2><?php echo htmlspecialchars($artista['nome']); ?></h2>
      
      <div class="bio-item">
        <p><?php echo nl2br(htmlspecialchars($artista['descricao'] ?? 'Artista que busca autonomia no mercado artístico, expondo seus desenhos manuais e digitais para Verseal.')); ?></p>
        <?php if (isArtista()): ?>
          <a class="btn-editar" href="editarbiografia.php">Editar Descrição</a>
        <?php endif; ?>
      </div>

      <h3>Contato</h3>
      
      <div class="bio-item">
        <p><strong>Telefone:</strong> <?php echo !empty($artista['telefone']) ? htmlspecialchars($artista['telefone']) : 'Não informado'; ?></p>
        <?php if (isArtista()): ?>
          <a class="btn-editar" href="editarbiografia.php">Editar Telefone</a>
        <?php endif; ?>
      </div>
      
      <div class="bio-item">
        <p><strong>Email:</strong> <?php echo !empty($artista['email']) ? htmlspecialchars($artista['email']) : 'Não informado'; ?></p>
        <?php if (isArtista()): ?>
          <a class="btn-editar" href="editarbiografia.php">Editar Email</a>
        <?php endif; ?>
      </div>
      
      <div class="bio-item">
        <p><strong>Instagram:</strong> <?php echo !empty($artista['instagram']) ? htmlspecialchars($artista['instagram']) : 'Não informado'; ?></p>
        <?php if (isArtista()): ?>
          <a class="btn-editar" href="editarbiografia.php">Editar Instagram</a>
        <?php endif; ?>
      </div>

      <?php if (isArtista()): ?>
      <div class="btn-container">
        <a href="editarbiografia.php" class="btn-completo">
          <i class="fas fa-edit"></i> Editar Biografia Completa
        </a>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <script>
    // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');
      if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          profileDropdown.style.display =
            profileDropdown.style.display === 'block' ? 'none' : 'block';
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

      // Forçar recarregamento da imagem para evitar cache
      const fotoPerfil = document.getElementById('foto-perfil-artista');
      if (fotoPerfil) {
        // Adiciona um timestamp para evitar cache
        const src = fotoPerfil.src;
        if (src && !src.includes('?')) {
          fotoPerfil.src = src + '?t=' + new Date().getTime();
        }
      }
    });

    // Verificar se há parâmetros de sucesso na URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
      Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: 'Biografia atualizada com sucesso!',
        timer: 3000,
        showConfirmButton: false
      });
      
      // Remover o parâmetro da URL sem recarregar a página
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  </script>
</body>
</html>