<?php
session_start();
require_once '../config/database.php'; // Arquivo de conexão com o banco

$usuarioLogado = $_SESSION["usuario"] ?? null;

// Buscar dados do artista logado do banco de dados
$artista = null;
if ($usuarioLogado && isset($usuarioLogado['nome'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM artistas WHERE nome = ? AND ativo = 1");
        $stmt->execute([$usuarioLogado['nome']]);
        $artista = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar artista: " . $e->getMessage());
    }
}

// Se não encontrou no banco, usar dados padrão
if (!$artista) {
    $artista = [
        "nome" => $usuarioLogado['nome'] ?? "Artista",
        "descricao" => "Artista que busca autonomia no mercado artístico, expondo seus desenhos manuais e digitais para Verseal.",
        "telefone" => "",
        "email" => "",
        "instagram" => "",
        "foto_perfil" => "../img/jamile.jpg"
    ];
}
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
      <a href="./perfil.php" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
      <div class="dropdown-content" id="profile-dropdown">
          <div class="user-info"><p>Seja bem-vindo, <?php echo htmlspecialchars($usuarioLogado['nome'] ?? 'Artista'); ?>!</p></div>
          <div class="dropdown-divider"></div>
          <a href="./artistaperfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
          <div class="dropdown-divider"></div>
          <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
      </div>
    </div>
  </nav>
</header>

  <!-- SEÇÃO BIOGRAFIA -->
  <section>
    <img src="<?php echo !empty($artista['foto_perfil']) ? $artista['foto_perfil'] : '../img/jamile.jpg'; ?>" 
         alt="<?php echo htmlspecialchars($artista['nome']); ?>">
    <div class="bio-texto">
      <h1>SOBRE</h1>
      <h2><?php echo htmlspecialchars($artista['nome']); ?></h2>
      <div class="bio-item">
        <p><?php echo htmlspecialchars($artista['descricao']); ?></p>
        <?php if (!empty($usuarioLogado)): ?>
          <a class="btn-editar" href="editarbiografia.php?campo=descricao">Editar Descrição</a>
        <?php endif; ?>
      </div>

      <h3>Contato</h3>
      <div class="bio-item">
        <p>Telefone: <?php echo !empty($artista['telefone']) ? htmlspecialchars($artista['telefone']) : 'Não informado'; ?></p>
        <?php if (!empty($usuarioLogado)): ?>
          <a class="btn-editar" href="editarbiografia.php?campo=telefone">Editar Telefone</a>
        <?php endif; ?>
      </div>
      <div class="bio-item">
        <p>Email: <?php echo !empty($artista['email']) ? htmlspecialchars($artista['email']) : 'Não informado'; ?></p>
        <?php if (!empty($usuarioLogado)): ?>
          <a class="btn-editar" href="editarbiografia.php?campo=email">Editar Email</a>
        <?php endif; ?>
      </div>
      <div class="bio-item">
        <p>Instagram: <?php echo !empty($artista['instagram']) ? htmlspecialchars($artista['instagram']) : 'Não informado'; ?></p>
        <?php if (!empty($usuarioLogado)): ?>
          <a class="btn-editar" href="editarbiografia.php?campo=instagram">Editar Instagram</a>
        <?php endif; ?>
      </div>
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
    });
  </script>
</body>
</html>