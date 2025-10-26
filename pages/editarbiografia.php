<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;

// Arquivo JSON que armazena os dados do artista
$arquivo = 'dados_artista.json';

// Inicializa dados se não existir
if (!file_exists($arquivo)) {
    $dados = [
        "nome" => "Jamile Franquilim",
        "descricao" => "Artista de 16 anos que busca autonomia no mercado artístico, expondo seus desenhos manuais e digitais para Verseal.",
        "data_nascimento" => "2009-01-01",
        "telefone" => "123456-7890",
        "email" => "jamyfranquilim@gmail.com",
        "social" => "@oliveirzz.a",
        "foto" => "../img/jamile.jpg"
    ];
    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));
} else {
    $dados = json_decode(file_get_contents($arquivo), true);
}

// Processa formulário ao salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome'] = $_POST['nome'];
    $dados['descricao'] = $_POST['descricao'];
    $dados['data_nascimento'] = $_POST['data_nascimento'];
    $dados['telefone'] = $_POST['telefone'];
    $dados['email'] = $_POST['email'];
    $dados['social'] = $_POST['social'];

    if (isset($_FILES['foto']) && $_FILES['foto']['tmp_name']) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $novoNome = '../img/artista.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $novoNome);
        $dados['foto'] = $novoNome;
    }

    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));

    // Redireciona para visualização da biografia
    header("Location: artistabiografia.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Biografia - Verseal</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<style>

/* FUNDO COM LINHAS DIAGONAIS */
body {
  background-color: #fff;
  background-image: repeating-linear-gradient(
    -45deg,
    #f6eae5 0px,
    #f6eae5 1px,
    transparent 1px,
    transparent 30px
  );
}

/* CONTAINER PRINCIPAL */
.edit-bio-container {
  max-width: 1100px;
  margin: 100px auto;
  background: #ffffff;
  border-radius: 25px;
  padding: 50px 70px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: flex-start;
  gap: 60px;
  position: relative;
}

/* TÍTULO COM PINCELADA */
.edit-bio-container::before {
  content: 'EDITAR BIOGRAFIA';
  position: absolute;
  top: -40px;
  left: 50%;
  transform: translateX(-50%);
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  color: #fff;
  background: url('../img/pincelada.png') no-repeat center/contain;
  padding: 15px 40px;
  text-align: center;
  font-weight: bold;
  letter-spacing: 2px;
}

/* FOTO */
.edit-bio-container .foto-area {
  flex: 1;
  text-align: center;
}

.edit-bio-container .foto-area img {
  width: 320px;
  height: 320px;
  object-fit: cover;
  border-radius: 20px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  margin-bottom: 15px;
}

.edit-bio-container .foto-area input[type="file"] {
  display: block;
  margin: 10px auto;
  font-size: 0.9rem;
  color: #444;
}

/* FORMULÁRIO */
.edit-bio-container form {
  flex: 1.2;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.edit-bio-container label {
  font-weight: 600;
  color: #444;
  font-size: 1rem;
}

.edit-bio-container input,
.edit-bio-container textarea,
.edit-bio-container select {
  width: 100%;
  font-family: 'Open Sans', sans-serif;
  padding: 12px 15px;
  border: 2px solid #f0dcd0;
  border-radius: 12px;
  font-size: 1rem;
  color: #333;
  outline: none;
  transition: all 0.3s ease;
  background: #fdf9f8;
}

.edit-bio-container input:focus,
.edit-bio-container textarea:focus,
.edit-bio-container select:focus {
  border-color: #e07b67;
  box-shadow: 0 0 8px rgba(224, 123, 103, 0.3);
}

/* CAMPOS EM DUAS COLUNAS (EMAIL + REDES SOCIAIS) */
.edit-bio-container .duo {
  display: flex;
  gap: 20px;
}

.edit-bio-container .duo input {
  flex: 1;
}

/* BOTÃO SALVAR */
.edit-bio-container button {
  align-self: center;
  margin-top: 20px;
  padding: 12px 40px;
  background: linear-gradient(135deg, #e07b67, #cc624e);
  color: #fff;
  border: none;
  border-radius: 30px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(204, 98, 78, 0.4);
  transition: all 0.3s ease;
}

.edit-bio-container button:hover {
  transform: translateY(-3px);
  background: linear-gradient(135deg, #cc624e, #e07b67);
  box-shadow: 0 10px 25px rgba(224, 123, 103, 0.5);
}
</style>
</head>
<header>
  <div class="logo">Verseal</div>
  
  <nav>
    <a href="artistahome.php">Início</a>
    <a href="./artistasobra.php">Obras</a>
    <a href="./artistabiografia.php">Artistas</a>
    
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

    <a href="./carrinho.php" class="icon-link" id="cart-icon"><i class="fas fa-shopping-cart"></i></a>
    <div class="profile-dropdown">
      <a href="./perfil.php" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
      <div class="dropdown-content" id="profile-dropdown">
        <?php if ($usuarioLogado): ?>
          <div class="user-info"><p>Seja bem-vindo, <?php echo htmlspecialchars($usuarioLogado); ?>!</p></div>
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
  </nav>
</header>
<body>
<div class="edit-bio-container">
  <div class="foto-area">
    <h1>Edite sua Biografia</h1>
    <img src="<?php echo $dados['foto']; ?>" alt="Foto da artista">
    <input type="file" name="foto" form="form-bio">
  </div>

  <form id="form-bio" action="" method="post" enctype="multipart/form-data">
      <label>Nome completo</label>
      <input type="text" name="nome" value="<?php echo htmlspecialchars($dados['nome']); ?>">

      <label>Descrição</label>
      <textarea name="descricao" rows="3"><?php echo htmlspecialchars($dados['descricao']); ?></textarea>

      <label>Data de nascimento</label>
      <input type="date" name="data_nascimento" value="<?php echo $dados['data_nascimento']; ?>">

      <label>Telefone</label>
      <input type="tel" name="telefone" value="<?php echo $dados['telefone']; ?>">

      <div class="duo">
        <div>
          <label>E-mail</label>
          <input type="email" name="email" value="<?php echo $dados['email']; ?>">
        </div>
        <div>
          <label>Redes Sociais</label>
          <input type="text" name="social" value="<?php echo $dados['social']; ?>">
        </div>
      </div>

      <button type="submit">Salvar</button>
  </form>
</div>
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
