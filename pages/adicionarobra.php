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
  <div class="edit-obra-container">
  <div class="foto-area">
    <img src="../img/obra.jpg" alt="Foto da obra">
    <input type="file" name="foto">
    <button>SALVAR</button>
  </div>

  <form action="" method="post">
    <label>Nome da Obra</label>
    <input type="text" name="nome" placeholder="Digite...">

    <label>Preço</label>
    <input type="number" name="preco" placeholder="Digite...">

    <label>Técnica/Estilo</label>
    <input type="text" name="tecnica" placeholder="Digite...">

    <label>Dimensões</label>
    <input type="number" name="dimensao" placeholder="Digite...">

    <label>Data de Criação</label>
    <input type="date" name="data_criacao" placeholder="Selecione...">

    <label>Palavras-chave</label>
    <input type="text" name="palavras_chave" placeholder="Digite...">
  </form>
</div>
</body>
</html>
