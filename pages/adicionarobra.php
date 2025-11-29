<?php
session_start();
$usuarioLogado = $_SESSION["usuario"] ?? null;

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
      font-family: 'Open Sans', sans-serif;
    }

    /* CONTAINER PRINCIPAL */
    .edit-obra-container {
      max-width: 1100px;
      margin: 100px auto 50px;
      background: #ffffff;
      border-radius: 25px;
      padding: 60px 70px;
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
      z-index: 1;
    }

    /* FOTO AREA */
    .foto-area {
      flex: 1;
      text-align: center;
      padding: 20px;
      background: #fdf9f8;
      border-radius: 20px;
      border: 2px dashed #f0dcd0;
    }

    .foto-area img {
      width: 100%;
      max-width: 320px;
      height: 320px;
      object-fit: cover;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      border: 2px solid #e07b67;
    }

    .file-input-wrapper {
      position: relative;
      display: inline-block;
      width: 100%;
      max-width: 250px;
    }

    .file-input-button {
      display: block;
      padding: 12px 20px;
      background: linear-gradient(135deg, #e07b67, #cc624e);
      color: white;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      box-shadow: 0 4px 15px rgba(204, 98, 78, 0.3);
      width: 100%;
    }

    .file-input-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(204, 98, 78, 0.4);
    }

    .file-input-wrapper input[type="file"] {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }

    .file-name {
      display: block;
      margin-top: 8px;
      font-size: 0.85rem;
      color: #666;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* FORMULÁRIO */
    form {
      flex: 1.2;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    form label {
      font-weight: 600;
      color: #444;
      font-size: 1rem;
      margin-bottom: 0;
    }

    form input,
    form textarea,
    form select {
      width: 100%;
      padding: 14px 16px;
      border: 2px solid #f0dcd0;
      border-radius: 12px;
      font-size: 1rem;
      color: #333;
      outline: none;
      transition: all 0.3s ease;
      background: #fdf9f8;
      box-sizing: border-box;
      font-family: 'Open Sans', sans-serif;
    }

    form input:focus,
    form textarea:focus,
    form select:focus {
      border-color: #e07b67;
      box-shadow: 0 0 0 3px rgba(224, 123, 103, 0.1);
      background: #fff;
    }

    form textarea {
      height: 120px;
      resize: vertical;
      line-height: 1.5;
    }

    /* GRID PARA CAMPOS MENORES */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    /* CHECKBOXES */
    .categorias-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-top: 5px;
    }

    .categoria-checkbox {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .categoria-checkbox:hover {
      background: #f0dcd0;
    }

    .categoria-checkbox input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: #e07b67;
    }

    .categoria-checkbox label {
      font-weight: 500;
      color: #555;
      cursor: pointer;
      margin: 0;
    }

    /* BOTÃO SALVAR */
    .form-actions {
      text-align: center;
      margin-top: 30px;
    }

    button[type="submit"] {
      padding: 15px 50px;
      background: linear-gradient(135deg, #e07b67, #cc624e);
      color: #fff;
      border: none;
      border-radius: 30px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(204, 98, 78, 0.4);
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    button[type="submit"]:hover {
      transform: translateY(-3px);
      background: linear-gradient(135deg, #cc624e, #e07b67);
      box-shadow: 0 12px 25px rgba(224, 123, 103, 0.5);
    }

    /* Preview da imagem */
    #imagePreview {
      width: 100%;
      max-width: 320px;
      height: 320px;
      object-fit: cover;
      border-radius: 15px;
      display: none;
      margin-bottom: 20px;
      border: 2px solid #e07b67;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .preview-placeholder {
      width: 100%;
      max-width: 320px;
      height: 320px;
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #6c757d;
      border: 2px dashed #dee2e6;
      margin-bottom: 20px;
    }

    .preview-placeholder i {
      font-size: 3rem;
      margin-bottom: 15px;
      color: #adb5bd;
    }

    .preview-placeholder span {
      font-size: 0.9rem;
      text-align: center;
    }

    /* HEADER STYLES */
    header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    /* RESPONSIVIDADE */
    @media (max-width: 968px) {
      .edit-obra-container {
        flex-direction: column;
        padding: 40px 30px;
        margin: 80px 20px;
        gap: 40px;
      }

      .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
      }

      .categorias-group {
        grid-template-columns: 1fr;
      }

      .edit-obra-container::before {
        font-size: 1.8rem;
        padding: 12px 30px;
        top: -30px;
      }
    }

    @media (max-width: 480px) {
      .edit-obra-container {
        padding: 30px 20px;
        margin: 70px 15px;
      }

      .edit-obra-container::before {
        font-size: 1.5rem;
        padding: 10px 25px;
        top: -25px;
      }

      form input,
      form textarea,
      form select {
        padding: 12px 14px;
      }
    }

    /* ESTILOS PARA O DROPDOWN DO PERFIL */
    .profile-dropdown {
      position: relative;
      display: inline-block;
    }

    .profile-dropdown .icon-link {
      color: #333;
      text-decoration: none;
      padding: 10px;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: color 0.3s;
    }

    .profile-dropdown .icon-link:hover {
      color: #e07b67;
    }

    .profile-dropdown .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      top: 100%;
      background: white;
      min-width: 280px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      border-radius: 10px;
      z-index: 1000;
      padding: 15px 0;
    }

    .profile-dropdown .dropdown-content.show {
      display: block;
    }

    .user-info {
      padding: 0 15px 10px;
      text-align: center;
      border-bottom: 1px solid #eee;
    }

    .user-info p {
      margin: 0;
      font-weight: 600;
      color: #333;
      font-size: 0.95rem;
    }

    .dropdown-divider {
      height: 1px;
      background: #eee;
      margin: 10px 0;
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 15px;
      color: #333;
      text-decoration: none;
      transition: background 0.3s;
      font-size: 0.9rem;
    }

    .dropdown-item:hover {
      background: #f8f9fa;
    }

    .dropdown-item.logout-btn {
      color: #dc3545;
    }

    .dropdown-item.logout-btn:hover {
      background: #ffe6e6;
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

      <!-- Perfil -->
      <div class="profile-dropdown">
        <a href="#" class="icon-link" id="profile-icon">
          <i class="fas fa-user"></i>
        </a>
        <div class="dropdown-content" id="profile-dropdown">
          <div class="user-info">
            <p>Bem-vindo, <span id="user-name"><?php echo htmlspecialchars(is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado); ?></span>!</p>
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
    <form action="" method="post" enctype="multipart/form-data">
      <div class="foto-area">
      <div id="previewContainer">
        <div class="preview-placeholder" id="previewPlaceholder">
          <i class="fas fa-image"></i>
          <span>Preview da imagem</span>
        </div>
        <img src="" alt="Preview da obra" id="imagePreview">
      </div>
      
      <div class="file-input-wrapper">
        <div class="file-input-button">
          <i class="fas fa-upload"></i> Escolher Imagem
        </div>
        <input type="file" name="imagem" id="imagemInput" accept="image/*">
      </div>
      <span id="file-name" class="file-name">Nenhum arquivo selecionado</span>
      <small style="color: #666; display: block; margin-top: 8px;">Formatos: JPG, PNG, GIF (Máx. 5MB)</small>
    </div>

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
          alert('A imagem deve ter no máximo 5MB');
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
      
      if (parseFloat(preco) <= 0) {
        e.preventDefault();
        alert('Por favor, insira um preço válido maior que zero.');
        return;
      }
      
      if (parseInt(ano) < 1900 || parseInt(ano) > 2030) {
        e.preventDefault();
        alert('Por favor, insira um ano válido entre 1900 e 2030.');
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
  </script>

</body>
</html>