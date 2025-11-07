<?php
session_start();

// Verificar se é um artista logado
if (!isset($_SESSION["artistas"])) {
    header("Location: login.php");
    exit;
}

$usuarioLogado = $_SESSION["artistas"];
$artistaId = $usuarioLogado['id'];

// Conexão com o banco para buscar dados atuais
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Buscar dados atuais do artista
$sql = "SELECT * FROM artistas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artistaId);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();

// Processa formulário ao salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $biografia = $_POST['biografia'] ?? $descricao; // Usar campo específico de biografia se existir
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $social = $_POST['social'];

    // Atualizar no banco de dados
    $sql_update = "UPDATE artistas SET nome = ?, descricao = ?, biografia = ?, data_nascimento = ?, telefone = ?, email = ?, instagram = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssssi", $nome, $descricao, $biografia, $data_nascimento, $telefone, $email, $social, $artistaId);
    
    if ($stmt_update->execute()) {
        // Atualizar foto se foi enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['tmp_name']) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $novoNome = '../img/artistas/artista_' . $artistaId . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $novoNome);
            
            // Atualizar no banco
            $sql_foto = "UPDATE artistas SET foto_perfil = ? WHERE id = ?";
            $stmt_foto = $conn->prepare($sql_foto);
            $stmt_foto->bind_param("si", $novoNome, $artistaId);
            $stmt_foto->execute();
        }
        
        // Atualizar dados na sessão
        $_SESSION["artista"]['nome'] = $nome;
        
        // Redirecionar para a página de artistas
        header("Location: artistas.php");
        exit;
    } else {
        $erro = "Erro ao atualizar perfil: " . $conn->error;
    }
}

$conn->close();
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
  margin: 0;
  padding: 0;
}

/* CONTAINER PRINCIPAL */
.edit-bio-container {
  max-width: 1100px;
  margin: 100px auto 50px;
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
  max-width: 250px;
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

.edit-bio-container textarea {
  min-height: 80px;
  resize: vertical;
}

/* Campo de biografia maior */
.edit-bio-container textarea[name="biografia"] {
  min-height: 120px;
}

/* CAMPOS EM DUAS COLUNAS (EMAIL + REDES SOCIAIS) */
.edit-bio-container .duo {
  display: flex;
  gap: 20px;
}

.edit-bio-container .duo > div {
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

/* Mensagem de erro */
.erro {
  color: #dc3545;
  text-align: center;
  margin-bottom: 15px;
  font-weight: 600;
}
</style>
</head>
<body>
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
          <div class="user-info"><p>Seja bem-vindo, <?php echo htmlspecialchars($usuarioLogado['nome']); ?>!</p></div>
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

<div class="edit-bio-container">
  <div class="foto-area">
    <img src="<?php echo !empty($dados['foto_perfil']) ? $dados['foto_perfil'] : '../img/jamile.jpg'; ?>" alt="Foto do artista">
    <input type="file" name="foto" form="form-bio" accept="image/*">
  </div>

  <form id="form-bio" action="" method="post" enctype="multipart/form-data">
    <?php if (isset($erro)): ?>
      <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>

    <label>Nome completo</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($dados['nome'] ?? ''); ?>" required>

    <label>Descrição curta</label>
    <textarea name="descricao" rows="3" placeholder="Uma breve descrição sobre você..." required><?php echo htmlspecialchars($dados['descricao'] ?? ''); ?></textarea>

    <label>Biografia completa</label>
    <textarea name="biografia" rows="5" placeholder="Conte sua história, inspirações, trajetória artística..."><?php echo htmlspecialchars($dados['biografia'] ?? $dados['descricao'] ?? ''); ?></textarea>

    <label>Data de nascimento</label>
    <input type="date" name="data_nascimento" value="<?php echo $dados['data_nascimento'] ?? ''; ?>">

    <label>Telefone</label>
    <input type="tel" name="telefone" value="<?php echo $dados['telefone'] ?? ''; ?>">

    <div class="duo">
      <div>
        <label>E-mail</label>
        <input type="email" name="email" value="<?php echo $dados['email'] ?? ''; ?>" required>
      </div>
      <div>
        <label>Instagram</label>
        <input type="text" name="social" value="<?php echo $dados['instagram'] ?? ''; ?>" placeholder="@seuinstagram">
      </div>
    </div>

    <button type="submit">Salvar Alterações</button>
  </form>
</div>

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

    // Preview da imagem ao selecionar
    const inputFile = document.querySelector('input[type="file"]');
    const imgPreview = document.querySelector('.foto-area img');
    
    if (inputFile && imgPreview) {
      inputFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            imgPreview.src = e.target.result;
          }
          reader.readAsDataURL(file);
        }
      });
    }
  });
</script>
</body>
</html>