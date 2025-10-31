<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["usuario"]) || !is_array($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$usuarioLogado = $_SESSION["usuario"];

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processar o formulário quando for enviado
$mensagem_sucesso = "";
$mensagem_erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_obra'])) {
    // Coletar dados do formulário
    $nome = $conn->real_escape_string($_POST['nome_obra']);
    $preco = floatval($_POST['preco']);
    $tecnica = $conn->real_escape_string($_POST['tecnica']);
    $dimensao = $conn->real_escape_string($_POST['dimensao']);
    $ano = intval($_POST['ano']);
    $material = $conn->real_escape_string($_POST['material']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $artista = $usuarioLogado['nome']; // Usar o nome do usuário logado como artista
    
    // Processar upload da imagem
    $img_path = "../img/imagem2.png"; // Imagem padrão
    
    if (isset($_FILES['imagem_obra']) && $_FILES['imagem_obra']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../img/obras/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['imagem_obra']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        // Mover arquivo para o diretório
        if (move_uploaded_file($_FILES['imagem_obra']['tmp_name'], $file_path)) {
            $img_path = str_replace('../', './', $file_path); // Ajustar caminho para salvar no banco
        }
    }
    
    // Inserir no banco de dados
    $sql = "INSERT INTO obras (img, nome, artista, preco, descricao, dimensao, tecnica, ano, material) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdsssis", $img_path, $nome, $artista, $preco, $descricao, $dimensao, $tecnica, $ano, $material);
    
    if ($stmt->execute()) {
        $obra_id = $stmt->insert_id;
        
        // Inserir categorias (se houver)
        if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
            foreach ($_POST['categorias'] as $categoria_nome) {
                // Buscar ID da categoria
                $sql_categoria = "SELECT id FROM categorias WHERE nome = ?";
                $stmt_categoria = $conn->prepare($sql_categoria);
                $stmt_categoria->bind_param("s", $categoria_nome);
                $stmt_categoria->execute();
                $result_categoria = $stmt_categoria->get_result();
                
                if ($categoria_row = $result_categoria->fetch_assoc()) {
                    $categoria_id = $categoria_row['id'];
                    
                    // Inserir na tabela obra_categoria
                    $sql_obra_categoria = "INSERT INTO obra_categoria (obra_id, categoria_id) VALUES (?, ?)";
                    $stmt_obra_categoria = $conn->prepare($sql_obra_categoria);
                    $stmt_obra_categoria->bind_param("ii", $obra_id, $categoria_id);
                    $stmt_obra_categoria->execute();
                }
            }
        }
        
        $mensagem_sucesso = "Obra cadastrada com sucesso!";
        
        // Redirecionar após 2 segundos
        echo "<script>
            setTimeout(function() {
                window.location.href = 'artistasobra.php?obra_nova=' + $obra_id;
            }, 2000);
        </script>";
        
    } else {
        $mensagem_erro = "Erro ao cadastrar obra: " . $conn->error;
    }
    
    $stmt->close();
}

// Buscar categorias disponíveis para o formulário
$categorias = [];
$sql_categorias = "SELECT id, nome FROM categorias";
$result_categorias = $conn->query($sql_categorias);
if ($result_categorias) {
    while ($categoria = $result_categorias->fetch_assoc()) {
        $categorias[] = $categoria;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Adicionar Obras - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/adicionar-obras.css" />
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .btn-salvar {
      background: #cc624e;
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: background 0.3s;
      margin: 20px auto;
      display: block;
    }
    .btn-salvar:hover {
      background: #e07b67;
    }
    .mensagem-sucesso {
      background: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      border: 1px solid #c3e6cb;
    }
    .mensagem-erro {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }
    .categorias-group {
      margin: 15px 0;
    }
    .categorias-checkboxes {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }
    .categoria-checkbox {
      display: flex;
      align-items: center;
      gap: 5px;
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

      <div class="profile-dropdown">
        <a href="#" class="icon-link" id="profile-icon">
          <i class="fas fa-user"></i>
        </a>
        <div class="dropdown-content" id="profile-dropdown">
          <?php if (isset($usuarioLogado) && !empty($usuarioLogado['nome'])): ?>
            <div class="user-info">
              <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>!</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
            <div class="dropdown-divider"></div>
            <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
            <a href="./login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>

  <!-- FORMULÁRIO ADICIONAR OBRAS -->
  <section class="adicionar-obras">
    <div class="container">
      <h1>ADICIONAR OBRAS</h1>
      
      <?php if ($mensagem_sucesso): ?>
        <div class="mensagem-sucesso">
          <i class="fas fa-check-circle"></i> <?php echo $mensagem_sucesso; ?>
        </div>
      <?php endif; ?>
      
      <?php if ($mensagem_erro): ?>
        <div class="mensagem-erro">
          <i class="fas fa-exclamation-triangle"></i> <?php echo $mensagem_erro; ?>
        </div>
      <?php endif; ?>
      
      <form class="form-obras" id="form-obras" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="salvar_obra" value="1">
        
        <div class="form-grid">
          <div class="form-column">
            <div class="form-group">
              <label for="nome-obra">Nome da Obra</label>
              <input type="text" id="nome-obra" name="nome_obra" placeholder="Digite..." required>
            </div>
            
            <div class="form-group">
              <label for="preco">Preço (R$)</label>
              <input type="number" id="preco" name="preco" step="0.01" min="0" placeholder="0.00" required>
            </div>
            
            <div class="form-group">
              <label for="tecnica">Técnica/Estilo</label>
              <select id="tecnica" name="tecnica" required>
                <option value="" disabled selected>Selecione</option>
                <option value="Manual">Manual</option>
                <option value="Digital">Digital</option>
                <option value="Pintura">Pintura</option>
                <option value="Escultura">Escultura</option>
                <option value="Fotografia">Fotografia</option>
                <option value="Técnica mista">Técnica mista</option>
                <option value="Pintura digital">Pintura digital</option>
                <option value="Expressionismo">Expressionismo</option>
                <option value="Abstração">Abstração</option>
                <option value="Figurativo">Figurativo</option>
                <option value="Realismo">Realismo</option>
                <option value="Urban sketching">Urban sketching</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="dimensao">Dimensão</label>
              <input type="text" id="dimensao" name="dimensao" placeholder="Ex: 50x70cm" required>
            </div>
            
            <div class="form-group">
              <label for="ano">Ano de Criação</label>
              <input type="number" id="ano" name="ano" min="1900" max="2030" value="<?php echo date('Y'); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="material">Material</label>
              <input type="text" id="material" name="material" placeholder="Ex: Óleo sobre tela, Aquarela..." required>
            </div>

            <div class="form-group">
              <label for="descricao">Descrição da Obra</label>
              <textarea id="descricao" name="descricao" placeholder="Descreva sua obra..." rows="4" required></textarea>
            </div>

            <!-- Categorias -->
            <div class="categorias-group">
              <label>Categorias</label>
              <div class="categorias-checkboxes">
                <?php foreach ($categorias as $categoria): ?>
                  <div class="categoria-checkbox">
                    <input type="checkbox" id="categoria_<?php echo $categoria['id']; ?>" 
                           name="categorias[]" value="<?php echo $categoria['nome']; ?>">
                    <label for="categoria_<?php echo $categoria['id']; ?>"><?php echo $categoria['nome']; ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          
          <div class="form-column">
            <div class="upload-area" id="upload-area">
              <div class="upload-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <h3>INSERIR IMAGEM</h3>
                <p>Arraste e solte ou clique para fazer upload</p>
                <span>PNG, JPG, JPEG até 10MB</span>
              </div>
              <input type="file" id="imagem-obra" name="imagem_obra" accept="image/*" hidden>
            </div>
            <div class="image-preview" id="image-preview"></div>
          </div>
        </div>
        
        <button type="submit" class="btn-salvar">
          <i class="fas fa-save"></i>
          SALVAR OBRA
        </button>
      </form>
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

  <script>
    // Upload de imagem
    const uploadArea = document.getElementById('upload-area');
    const imageInput = document.getElementById('imagem-obra');
    const imagePreview = document.getElementById('image-preview');

    uploadArea.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
          uploadArea.style.display = 'none';
        }
        reader.readAsDataURL(file);
      }
    });

    // Permitir arrastar e soltar
    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.style.background = '#f0f0f0';
    });

    uploadArea.addEventListener('dragleave', () => {
      uploadArea.style.background = '';
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.style.background = '';
      const file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        imageInput.files = e.dataTransfer.files;
        const event = new Event('change');
        imageInput.dispatchEvent(event);
      }
    });

    // Validação do formulário
    document.getElementById('form-obras').addEventListener('submit', function(e) {
      const preco = document.getElementById('preco').value;
      if (preco <= 0) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Preço inválido',
          text: 'O preço deve ser maior que zero.'
        });
      }
    });

    // Dropdown do perfil
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileIcon && profileDropdown) {
      profileIcon.addEventListener('click', function(e){
        e.preventDefault();
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
      });

      document.addEventListener('click', function(e){
        if(!profileDropdown.contains(e.target) && e.target !== profileIcon){
          profileDropdown.style.display = 'none';
        }
      });
    }
  </script>
</body>
</html>