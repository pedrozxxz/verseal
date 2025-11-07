<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;

if (!$usuarioLogado) {
    header("Location: login.php");
    exit;
}

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"] ?? '';
    $preco = $_POST["preco"] ?? '';
    $tecnica = $_POST["tecnica"] ?? '';
    $dimensoes = $_POST["dimensoes"] ?? '';
    $ano = $_POST["ano"] ?? '';
    $material = $_POST["material"] ?? '';
    $descricao = $_POST["descricao"] ?? '';
    $categorias = $_POST["categorias"] ?? [];

    $categorias_json = json_encode($categorias);

    // Upload da imagem
    $imagem_url = '';
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/obras/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_file)) {
            $imagem_url = 'img/obras/' . $file_name;
        }
    }

    // Inserir obra no banco
    $sql = "INSERT INTO produtos (nome, artista, preco, descricao, dimensoes, tecnica, ano, material, categorias, imagem_url, ativo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $artista_nome = is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado;
    $stmt->bind_param("ssdsssisss", $nome, $artista_nome, $preco, $descricao, $dimensoes, $tecnica, $ano, $material, $categorias_json, $imagem_url);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Obra adicionada!',
                text: 'Sua obra foi cadastrada com sucesso.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'artistasobra.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Não foi possível cadastrar a obra.',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Buscar obras do artista
$artista_nome = is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado;
$stmt = $conn->prepare("SELECT * FROM produtos WHERE artista = ? ORDER BY id DESC");
$stmt->bind_param("s", $artista_nome);
$stmt->execute();
$result = $stmt->get_result();
$obras = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verseal - Adicionar Obra</title>
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
  margin: 0;
  padding: 0;
}

/* CONTAINER PRINCIPAL */
.edit-obra-container {
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
.edit-obra-container::before {
  content: 'ADICIONAR OBRA';
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
.edit-obra-container .foto-area {
  flex: 1;
  text-align: center;
}

.edit-obra-container .foto-area img {
  width: 320px;
  height: 320px;
  object-fit: cover;
  border-radius: 20px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  margin-bottom: 15px;
  border: 2px dashed #e07b67;
}

.edit-obra-container .foto-area input[type="file"] {
  display: block;
  margin: 10px auto;
  font-size: 0.9rem;
  color: #444;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 8px;
  width: 100%;
}

/* FORMULÁRIO */
.edit-obra-container form {
  flex: 1.2;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.edit-obra-container label {
  font-weight: 600;
  color: #444;
  font-size: 1rem;
  margin-bottom: 5px;
}

.edit-obra-container input,
.edit-obra-container textarea,
.edit-obra-container select {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #f0dcd0;
  border-radius: 12px;
  font-size: 1rem;
  color: #333;
  outline: none;
  transition: all 0.3s ease;
  background: #fdf9f8;
  box-sizing: border-box;
}

.edit-obra-container input:focus,
.edit-obra-container textarea:focus,
.edit-obra-container select:focus {
  border-color: #e07b67;
  box-shadow: 0 0 8px rgba(224, 123, 103, 0.3);
}

.edit-obra-container textarea {
  height: 100px;
  resize: vertical;
}

/* CHECKBOXES DE CATEGORIAS */
.categorias-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 5px;
}

.categoria-checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
}

.categoria-checkbox input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.categoria-checkbox label {
  font-weight: normal;
  margin: 0;
  font-size: 0.9rem;
}

/* BOTÃO SALVAR */
.edit-obra-container button[type="submit"] {
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
  width: auto;
}

.edit-obra-container button[type="submit"]:hover {
  transform: translateY(-3px);
  background: linear-gradient(135deg, #cc624e, #e07b67);
  box-shadow: 0 10px 25px rgba(224, 123, 103, 0.5);
}

/* Preview da imagem */
#imagePreview {
  max-width: 100%;
  max-height: 300px;
  display: none;
  margin-top: 10px;
  border-radius: 10px;
}

/* Para telas menores */
@media (max-width: 768px) {
  .edit-obra-container {
    flex-direction: column;
    padding: 30px;
    margin: 80px 20px;
  }
  
  .categorias-group {
    grid-template-columns: 1fr;
  }
}
  </style>
</head>

<body>

  <!-- HEADER -->
  <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="artistahome.php"><i class="fas fa-home"></i> Início</a>
      <a href="artistasobra.php"><i class="fas fa-palette"></i> Obras</a>
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
              <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars(is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado); ?></span>!</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="artistabiografia.php" class="dropdown-item">
              <i class="fas fa-user-circle"></i> Meu Perfil
            </a>
            <a href="artistasobra.php" class="dropdown-item">
              <i class="fas fa-palette"></i> Minhas Obras
            </a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item logout-btn">
              <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
      </div>
    </nav>
  </header>

  <!-- FORMULÁRIO DE ADIÇÃO DE OBRA -->
  <div class="edit-obra-container">
    <div class="foto-area">
      <img src="../img/placeholder-obra.jpg" alt="Preview da obra" id="imagePreview">
      <input type="file" name="imagem" id="imagemInput" accept="image/*">
      <small>Selecione uma imagem para a obra</small>
    </div>

    <form action="" method="post" enctype="multipart/form-data">
      <label for="nome">Nome da Obra</label>
      <input type="text" name="nome" id="nome" placeholder="Digite o nome da obra..." required>

      <label for="preco">Preço (R$)</label>
      <input type="number" name="preco" id="preco" placeholder="0.00" step="0.01" min="0" required>

      <label for="descricao">Descrição da Obra</label>
      <textarea name="descricao" id="descricao" placeholder="Descreva sua obra..." required></textarea>

      <label for="tecnica">Técnica/Estilo</label>
      <input type="text" name="tecnica" id="tecnica" placeholder="Ex: Pintura a óleo, Digital..." required>

      <label for="dimensoes">Dimensões</label>
      <input type="text" name="dimensoes" id="dimensoes" placeholder="Ex: 50x70cm" required>

      <label for="ano">Ano de Criação</label>
      <input type="number" name="ano" id="ano" placeholder="2024" min="1900" max="2030" required>

      <label for="material">Material</label>
      <input type="text" name="material" id="material" placeholder="Ex: Tinta acrílica, Tela..." required>

      <label>Categorias</label>
      <div class="categorias-group">
        <div class="categoria-checkbox">
          <input type="checkbox" name="categorias[]" value="manual" id="cat_manual">
          <label for="cat_manual">Manual</label>
        </div>
        <div class="categoria-checkbox">
          <input type="checkbox" name="categorias[]" value="digital" id="cat_digital">
          <label for="cat_digital">Digital</label>
        </div>
        <div class="categoria-checkbox">
          <input type="checkbox" name="categorias[]" value="preto e branco" id="cat_pb">
          <label for="cat_pb">Preto e Branco</label>
        </div>
        <div class="categoria-checkbox">
          <input type="checkbox" name="categorias[]" value="colorido" id="cat_colorido">
          <label for="cat_colorido">Colorido</label>
        </div>
      </div>

      <button type="submit">
        <i class="fas fa-plus"></i> Adicionar Obra
      </button>
    </form>
  </div>

  <script>
    // Preview da imagem
    document.getElementById('imagemInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('imagePreview');
      
      if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
      }
    });

    // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function() {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');
      
      if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function(e) {
          if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
            profileDropdown.style.display = 'none';
          }
        });
      }
    });
  </script>
</body>
</html>