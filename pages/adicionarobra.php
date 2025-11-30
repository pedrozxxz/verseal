DEIXE O FORMULARIO NA DIREITA PELO AMOR DE DEUS

<?php
session_start();
$usuarioLogado = $_SESSION["usuario"] ?? null;

require_once 'config.php';

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

$total_nao_lidas = 0;
if (isset($usuarioLogado['id']) && function_exists('getTotalMensagensNaoLidas')) {
    $total_nao_lidas = getTotalMensagensNaoLidas($conn, $usuarioLogado['id']);
}

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"] ?? '';
    $preco = $_POST["preco"] ?? 0;
    $tecnica = $_POST["tecnica"] ?? '';
    $dimensoes = $_POST["dimensoes"] ?? '';
    $ano = $_POST["ano"] ?? '';
    $material = $_POST["material"] ?? '';
    $descricao = $_POST["descricao"] ?? '';
    $categorias = $_POST["categorias"] ?? [];

    $categorias_json = json_encode($categorias);

    // Upload da imagem - SOLUÇÃO AUTOMÁTICA
$imagem_url = '';
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    // 🔹 DETECTA AUTOMATICAMENTE A ESTRUTURA
    $base_path = $_SERVER['DOCUMENT_ROOT'] . '/verseal/';
    
    // Verifica se existe a pasta verseal dentro de verseal
    if (is_dir($base_path . 'verseal/')) {
        $upload_dir = $base_path . 'verseal/img/obras/';
    } else {
        $upload_dir = $base_path . 'img/obras/';
    }
    
    // Criar diretório se não existir
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Verificar extensões permitidas
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Formato de arquivo não permitido. Use apenas JPG, PNG ou GIF.',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_file)) {
            $imagem_url = 'img/obras/' . $file_name;
            
            // 🔹 DEBUG: Verificar se está funcionando
            echo "<script>console.log('Arquivo salvo em: $upload_file');</script>";
        } else {
            error_log("Erro ao mover arquivo para: " . $upload_file);
        }
    }
}

    // Inserir obra no banco - VERIFICAR SE A IMAGEM_URL ESTÁ PREENCHIDA
    if (!empty($imagem_url)) {
        $sql = "INSERT INTO produtos (nome, artista, preco, descricao, dimensoes, tecnica, ano, material, categorias, imagem_url, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die("Erro na preparação da query: " . $conn->error);
        }

        $artista_nome = is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado;
        $stmt->bind_param("ssdsssisss", $nome, $artista_nome, $preco, $descricao, $dimensoes, $tecnica, $ano, $material, $categorias_json, $imagem_url);

        if ($stmt->execute()) {
            header("Location: artistahome.php");
            exit;
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Não foi possível cadastrar a obra: " . addslashes($conn->error) . "',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Não foi possível fazer upload da imagem. Verifique se o arquivo é válido.',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Fechar conexão
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verseal - Adicionar Obra</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
   /* ==== ESTILO GERAL ==== */
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
  font-family: 'Open Sans', sans-serif;
  min-height: 100vh;
}

/* ==== LAYOUT PRINCIPAL ==== */
.edit-obra-container {
  width: 100%;
  max-width: 1300px;
  margin: 120px auto;
  background: #ffffff;
  border-radius: 25px;
  padding: 60px;
  display: grid;
  grid-template-columns: 1fr 420px; /* FORMULÁRIO À ESQUERDA, IMAGEM À DIREITA */
  gap: 50px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  position: relative;
  min-height: 600px;
}

/* ==== TÍTULO ==== */
.edit-obra-container::before {
  content: 'ADICIONAR OBRA';
  position: absolute;
  top: -40px;
  left: 50%;
  transform: translateX(-50%);
  background: url('../img/pincelada.png') no-repeat center/contain;
  color: #fff;
  font-size: 2.2rem;
  padding: 15px 40px;
  font-family: 'Playfair Display', serif;
  letter-spacing: 2px;
  font-weight: bold;
}

/* ==== ÁREA DA IMAGEM - AGORA NA DIREITA ==== */
.foto-area {
  background: #fdf9f8;
  border-radius: 20px;
  padding: 25px;
  border: 2px dashed #f0dcd0;
  text-align: center;
  height: fit-content;
  position: sticky;
  top: 140px; /* FIXO NA TELA */
  align-self: start;
}

.foto-area img,
#imagePreview {
  width: 100%;
  height: 330px;
  object-fit: cover;
  border-radius: 16px;
  border: 2px solid #e07b67;
  display: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.preview-placeholder {
  width: 100%;
  height: 330px;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-radius: 16px;
  border: 2px dashed #dee2e6;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  color: #6c757d;
  margin-bottom: 15px;
}

.preview-placeholder i {
  font-size: 3rem;
  margin-bottom: 15px;
}

/* BOTÃO DE UPLOAD */
.file-input-wrapper {
  width: 100%;
  position: relative;
  margin-bottom: 10px;
}

.file-input-button {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  color: #fff;
  padding: 12px;
  border-radius: 25px;
  cursor: pointer;
  text-align: center;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(204, 98, 78, 0.3);
  transition: all 0.3s ease;
}

.file-input-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(204, 98, 78, 0.4);
}

.file-input-wrapper input[type="file"] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}

.file-name {
  display: block;
  margin-top: 8px;
  font-size: .85rem;
  color: #555;
}

/* ==== FORMULÁRIO - AGORA À ESQUERDA ==== */
form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

form input,
form textarea,
form select {
  padding: 13px 15px;
  border: 2px solid #f0dcd0;
  background: #fdf9f8;
  border-radius: 12px;
  font-size: 1rem;
  transition: .3s;
  font-family: 'Open Sans', sans-serif;
}

form input:focus,
form textarea:focus,
form select:focus {
  border-color: #e07b67;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(224, 123, 103, .15);
  outline: none;
}

textarea {
  height: 130px;
  resize: none;
}

/* CAMPOS EM 2 COLUNAS */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

/* CATEGORIAS */
.categorias-group {
  display: grid;
  grid-template-columns: repeat(2,1fr);
  gap: 12px;
}

.categoria-checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8f9fa;
  padding: 10px;
  border-radius: 8px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.categoria-checkbox:hover {
  background: #f0dcd0;
}

.categoria-checkbox input[type="checkbox"] {
  cursor: pointer;
}

.categoria-checkbox label {
  cursor: pointer;
  margin: 0;
}

/* BOTÃO SALVAR */
button[type="submit"] {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  border: none;
  padding: 15px 30px;
  border-radius: 30px;
  color: #fff;
  font-size: 1.1rem;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(204, 98, 78, .4);
  width: 100%;
  max-width: 300px;
  align-self: center;
  transition: all 0.3s ease;
  font-weight: 600;
}

button[type="submit"]:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 25px rgba(204, 98, 78, .5);
}

/* ELEMENTOS FIXOS */
header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

.edit-obra-container {
  position: relative;
  z-index: 1;
}

/* RESPONSIVIDADE */
@media (max-width: 980px) {
  .edit-obra-container {
    grid-template-columns: 1fr;
    padding: 40px 20px;
    margin: 100px auto;
    gap: 30px;
  }
  
  .foto-area {
    position: relative;
    top: 0;
    order: 1;
  }
  
  form {
    order: 2;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .edit-obra-container::before {
    font-size: 1.8rem;
    padding: 12px 30px;
    top: -30px;
  }
}

@media (max-width: 768px) {
  .categorias-group {
    grid-template-columns: 1fr;
  }
  
  .edit-obra-container {
    padding: 30px 15px;
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

      <?php if (!empty($usuarioLogado['id'])): ?>
      <div class="notificacao-mensagens">
          <a href="artistaperfil.php?aba=mensagens" class="icon-link">
              <i class="fas fa-envelope"></i>
              
              <?php if ($total_nao_lidas > 0): ?>
                <span class="mensagens-badge" id="mensagensBadge"><?php echo $total_nao_lidas; ?></span>
              <?php endif; ?>
          </a>
      </div>
      <?php endif; ?>

      <!-- DROPDOWN PERFIL -->
      <div class="profile-dropdown">
          <a href="#" class="icon-link" id="profile-icon">
            <i class="fas fa-user"></i>
          </a>

          <div class="dropdown-content" id="profile-dropdown">
            <?php if (!empty($usuarioLogado['nome'])): ?>
              <div class="user-info">
                <p>Bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>!</p>
                <small><?php echo ucfirst($tipoUsuario); ?></small>
              </div>

              <div class="dropdown-divider"></div>
              <a href="./artistaperfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
              <a href="./editarbiografia.php" class="dropdown-item"><i class="fas fa-edit"></i> Editar Biografia</a>

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

  <!-- FORMULÁRIO DE ADIÇÃO DE OBRA -->
  <div class="edit-obra-container">
    <!-- FORMULÁRIO À ESQUERDA -->
    <form action="" method="post" enctype="multipart/form-data">
      <!-- INPUT DO FILE DENTRO DO FORMULÁRIO (ESCONDIDO) -->
      <input type="file" name="imagem" id="imagemInput" accept="image/*" style="display: none;">
      
      <div class="form-group">
        <label for="nome">Nome da Obra</label>
        <input type="text" name="nome" id="nome" placeholder="Digite o nome da obra..." required>
      </div>

      <div class="form-group">
        <label for="preco">Preço (R$)</label>
        <input type="number" name="preco" id="preco" placeholder="0.00" step="0.01" min="0" required>
      </div>

      <div class="form-group">
        <label for="descricao">Descrição da Obra</label>
        <textarea name="descricao" id="descricao" placeholder="Descreva sua obra..." required></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="tecnica">Técnica/Estilo</label>
          <input type="text" name="tecnica" id="tecnica" placeholder="Ex: Pintura a óleo, Digital..." required>
        </div>

        <div class="form-group">
          <label for="dimensoes">Dimensões</label>
          <input type="text" name="dimensoes" id="dimensoes" placeholder="Ex: 50x70cm" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="ano">Ano de Criação</label>
          <input type="number" name="ano" id="ano" placeholder="2024" min="1900" max="2030" required>
        </div>

        <div class="form-group">
          <label for="material">Material</label>
          <input type="text" name="material" id="material" placeholder="Ex: Tinta acrílica, Tela..." required>
        </div>
      </div>

      <div class="form-group">
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
      </div>

      <div class="form-actions">
        <button type="submit">
          <i class="fas fa-plus"></i> Adicionar Obra
        </button>
      </div>
    </form>

    <!-- ÁREA DA IMAGEM À DIREITA -->
    <div class="foto-area">
      <div id="previewContainer">
        <div class="preview-placeholder" id="previewPlaceholder">
          <i class="fas fa-image"></i>
          <span>Preview da imagem</span>
        </div>
        <img src="" alt="Preview da obra" id="imagePreview">
      </div>
      
      <!-- BOTÃO PARA ACIONAR O INPUT FILE -->
      <div class="file-input-wrapper">
        <div class="file-input-button" onclick="document.getElementById('imagemInput').click()">
          <i class="fas fa-upload"></i> Escolher Imagem
        </div>
      </div>
      <span id="file-name" class="file-name">Nenhum arquivo selecionado</span>
      <small style="color: #666; display: block; margin-top: 8px;">Formatos: JPG, PNG, GIF (Máx. 5MB)</small>
    </div>
  </div>

  <script>
    // Preview da imagem
    document.getElementById('imagemInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('imagePreview');
      const placeholder = document.getElementById('previewPlaceholder');
      const fileName = document.getElementById('file-name');
      
      if (file) {
        // Verificar tamanho do arquivo (máximo 5MB)
        if (file.size > 5 * 1024 * 1024) {
          Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'A imagem deve ter no máximo 5MB',
            confirmButtonText: 'OK'
          });
          this.value = '';
          fileName.textContent = 'Arquivo muito grande (máx. 5MB)';
          fileName.style.color = '#e74c3c';
          return;
        }
        
        fileName.textContent = file.name;
        fileName.style.color = '#27ae60';
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
          placeholder.style.display = 'none';
        }
        
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
        placeholder.style.display = 'flex';
        fileName.textContent = 'Nenhum arquivo selecionado';
        fileName.style.color = '#666';
      }
    });

    // Dropdown Perfil
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

        profileDropdown.addEventListener('click', function(e) {
          e.stopPropagation();
        });
      }
    });

    // Validação do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
      const preco = document.getElementById('preco').value;
      const ano = document.getElementById('ano').value;
      const imagemInput = document.getElementById('imagemInput');
      
      if (parseFloat(preco) <= 0) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Erro!',
          text: 'Por favor, insira um preço válido maior que zero.',
          confirmButtonText: 'OK'
        });
        return;
      }
      
      if (parseInt(ano) < 1900 || parseInt(ano) > 2030) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Erro!',
          text: 'Por favor, insira um ano válido entre 1900 e 2030.',
          confirmButtonText: 'OK'
        });
        return;
      }
      
      if (!imagemInput.files || !imagemInput.files[0]) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Erro!',
          text: 'Por favor, selecione uma imagem para a obra.',
          confirmButtonText: 'OK'
        });
        return;
      }
    });

    // Debug do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
      const formData = new FormData(this);
      
      console.log('Dados do formulário:');
      for (let [key, value] of formData.entries()) {
        if (key === 'imagem') {
          console.log('Imagem:', value.name, value.size, 'bytes');
        } else {
          console.log(key + ':', value);
        }
      }
    });

    function atualizarBadgeMensagens() {
      const badge = document.getElementById('mensagensBadge');
      const totalNaoLidas = <?php echo $total_nao_lidas; ?>;

      if (badge) {
        if (totalNaoLidas > 0) {
          badge.textContent = totalNaoLidas;
          badge.style.display = 'flex';
        } else {
          badge.style.display = 'none';
        }
      }
    }

    atualizarBadgeMensagens();
  </script>

</body>
</html>