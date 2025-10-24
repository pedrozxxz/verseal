<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verseal - Obras de Arte</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
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

section h1 {
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

section h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: #333;
  margin-bottom: 15px;
}

section p {
  font-size: 1rem;
  color: #555;
  margin-bottom: 12px;
  line-height: 1.6;
}

section h3 {
  font-family: 'Playfair Display', serif;
  color: #e07b67;
  margin-top: 25px;
  font-size: 1.4rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

button {
  display: block;
  margin: 10px auto 0;
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

button:hover {
  transform: translateY(-3px);
  background: linear-gradient(135deg, #cc624e, #e07b67);
  box-shadow: 0 10px 25px rgba(224, 123, 103, 0.5);
}
  </style>
</head>

<body>

  <!-- HEADER reaproveitado -->
  <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="artistahome.php"><i class="fas fa-home"></i> Início</a>
      <a href="artistasobras.php"><i class="fas fa-palette"></i> Obras</a>
      <a href="artistabiografia.php"><i class="fas fa-user"></i> Quem eu sou?</a>
      
      <!-- Menu Hamburguer Flutuante -->
      <div class="hamburger-menu-desktop">
        <input type="checkbox" id="menu-toggle-desktop">
        <label for="menu-toggle-desktop" class="hamburger-desktop">
          <i class="fas fa-bars"></i>
          <span>ACESSO</span>
        </label>
        <div class="menu-content-desktop">
          <div class="menu-section">
            <a href="../index.php" class="menu-item">
              <i class="fas fa-user"></i>
              <span>Cliente</span>
            </a>
            <a href="./admhome.php" class="menu-item">
              <i class="fas fa-user-shield"></i>
              <span>ADM</span>
            </a>
            <a href="./artistahome.php" class="menu-item active">
              <i class="fas fa-palette"></i>
              <span>Artista</span>
            </a>
          </div>
        </div>
      </div>

      <div class="profile-dropdown">
        <a href="#" class="icon-link" id="profile-icon">
          <i class="fas fa-user"></i>
        </a>
        <div class="dropdown-content" id="profile-dropdown">
            <div class="user-info">
              <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado); ?></span>!</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-user-circle"></i> Meu Perfil
            </a>
            <a href="#" class="dropdown-item">
              <i class="fas fa-shopping-bag"></i> Minhas Vendas
            </a>
            <a href="#" class="dropdown-item">
              <i class="fas fa-heart"></i> Favoritos
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item logout-btn">
              <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
      </div>
    </nav>
  </header>
  <section>
  <img src="../img/jamile.jpg" alt="Jamile Franquilim">
  <div class="bio-texto">
    <h1>SOBRE</h1>
    <h2>Jamile Franquilim</h2>
    <p>Artista de 16 anos que busca autonomia no mercado artístico, expondo seus desenhos manuais e digitais para Verseal.</p>

    <h3>Contato</h3>
    <p>Telefone: 123456-7890</p>
    <p>Email: jamyfranquilim@gmail.com</p>
    <p>Instagram: @oliveirzz.a</p>
  </div>
</section>
<button><a href="editarbiografia.php">Editar</a></button>
</body>
</html>
