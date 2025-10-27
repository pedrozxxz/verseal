<?php
session_start();

// Verificar se os produtos estão na sessão, se não, inicializar
if (!isset($_SESSION['produtos'])) {
    // Redirecionar para artistasobra.php para inicializar os produtos
    header('Location: artistasobra.php');
    exit;
}

$produtos = $_SESSION['produtos'];

// Obter ID da obra da URL
$obraId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$obra = isset($produtos[$obraId]) ? $produtos[$obraId] : null;

if (!$obra) {
    header('Location: artistasobra.php');
    exit;
}

// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_obra'])) {
    // Atualizar os dados da obra
    $produtos[$obraId]['nome'] = $_POST['nome_obra'];
    $produtos[$obraId]['preco'] = floatval($_POST['preco']);
    $produtos[$obraId]['tecnica'] = $_POST['tecnica'];
    $produtos[$obraId]['dimensao'] = $_POST['dimensao'];
    $produtos[$obraId]['ano'] = intval($_POST['ano']);
    $produtos[$obraId]['material'] = $_POST['material'];
    $produtos[$obraId]['desc'] = $_POST['descricao'];
    
    // Atualizar na sessão
    $_SESSION['produtos'] = $produtos;
    
    // Redirecionar para a página de obras com parâmetro para mostrar a obra editada
    header('Location: artistasobra.php?obra_editada=' . $obraId . '&ordenacao=recentes');
    exit;
}

// Atualizar a variável $obra com os dados mais recentes
$obra = $produtos[$obraId];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editar Obra - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet" />
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
    }
    .btn-salvar:hover {
      background: #cc624e;
    }
    .btn-excluir {
      background: #dc3545;
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
    }
    .btn-excluir:hover {
      background: #c82333;
    }
    .btn-cancelar {
      background: #6c757d;
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
    }
    .btn-cancelar:hover {
      background: #5a6268;
    }
    .botoes-acoes {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
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

      <div class="profile-dropdown">
        <a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
        <div class="dropdown-content" id="profile-dropdown">
          <div class="user-info">
            <p>Faça login para acessar seu perfil</p>
          </div>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
          <a href="#" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
        </div>
      </div>
    </nav>
  </header>

  <!-- FORMULÁRIO EDITAR OBRAS -->
  <section class="adicionar-obras">
  <div class="container">
    <h1>EDITAR OBRAS</h1>

    <form class="form-obras" id="form-obras" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="salvar_obra" value="1">
      
      <div class="form-grid">
        <div class="form-column">

          <!-- Seleção da Obra -->
          <div class="form-group">
            <label for="select-obra">Selecione a Obra</label>
            <select id="select-obra" name="obra_id" onchange="carregarObra(this.value)">
              <?php foreach ($produtos as $id => $prod): ?>
              <option value="<?php echo $id; ?>" <?php echo $id == $obraId ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($prod['nome']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="nome-obra">Nome da Obra</label>
            <input type="text" id="nome-obra" name="nome_obra" placeholder="Digite..." value="<?php echo htmlspecialchars($obra['nome']); ?>" required>
          </div>

          <div class="form-group">
            <label for="preco">Preço</label>
            <input type="number" id="preco" name="preco" step="0.01" placeholder="Digite..." value="<?php echo $obra['preco']; ?>" required>
          </div>

          <div class="form-group">
            <label for="tecnica">Técnica/Estilo</label>
            <select id="tecnica" name="tecnica" required>
              <option value="manual" <?php echo $obra['tecnica'] == 'manual' ? 'selected' : ''; ?>>Manual</option>
              <option value="nft" <?php echo $obra['tecnica'] == 'nft' ? 'selected' : ''; ?>>NFT</option>
              <option value="mesa-digital" <?php echo $obra['tecnica'] == 'mesa-digital' ? 'selected' : ''; ?>>Mesa Digital</option>
              <option value="pintura" <?php echo $obra['tecnica'] == 'pintura' ? 'selected' : ''; ?>>Pintura</option>
              <option value="escultura" <?php echo $obra['tecnica'] == 'escultura' ? 'selected' : ''; ?>>Escultura</option>
              <option value="fotografia" <?php echo $obra['tecnica'] == 'fotografia' ? 'selected' : ''; ?>>Fotografia</option>
              <option value="Técnica mista" <?php echo $obra['tecnica'] == 'Técnica mista' ? 'selected' : ''; ?>>Técnica mista</option>
              <option value="Pintura digital" <?php echo $obra['tecnica'] == 'Pintura digital' ? 'selected' : ''; ?>>Pintura digital</option>
              <option value="Expressionismo" <?php echo $obra['tecnica'] == 'Expressionismo' ? 'selected' : ''; ?>>Expressionismo</option>
              <option value="Abstração" <?php echo $obra['tecnica'] == 'Abstração' ? 'selected' : ''; ?>>Abstração</option>
              <option value="Figurativo" <?php echo $obra['tecnica'] == 'Figurativo' ? 'selected' : ''; ?>>Figurativo</option>
              <option value="Realismo" <?php echo $obra['tecnica'] == 'Realismo' ? 'selected' : ''; ?>>Realismo</option>
              <option value="Urban sketching" <?php echo $obra['tecnica'] == 'Urban sketching' ? 'selected' : ''; ?>>Urban sketching</option>
            </select>
          </div>

          <div class="form-group">
            <label for="dimensao">Dimensão</label>
            <input type="text" id="dimensao" name="dimensao" placeholder="Digite..." value="<?php echo htmlspecialchars($obra['dimensao']); ?>" required>
          </div>

          <div class="form-group">
            <label for="ano">Ano de Criação</label>
            <input type="number" id="ano" name="ano" min="1900" max="2030" value="<?php echo $obra['ano']; ?>" required>
          </div>

          <div class="form-group">
            <label for="material">Material</label>
            <input type="text" id="material" name="material" placeholder="Digite..." value="<?php echo htmlspecialchars($obra['material']); ?>" required>
          </div>

          <div class="form-group">
            <label for="descricao">Descrição da Obra</label>
            <textarea id="descricao" name="descricao" placeholder="Descreva a obra..." rows="4" required><?php echo htmlspecialchars($obra['desc']); ?></textarea>
          </div>
        </div>

        <div class="form-column">
          <div class="upload-area" id="upload-area">
            <div class="upload-content">
              <i class="fas fa-cloud-upload-alt"></i>
              <h3>IMAGEM ATUAL</h3>
              <p>Clique para alterar a imagem</p>
              <span>PNG, JPG, JPEG até 10MB</span>
            </div>
            <input type="file" id="nova-imagem" name="nova_imagem" accept="image/*" hidden onchange="previewImage(this)">
          </div>

          <div class="image-preview" id="image-preview">
            <img src="<?php echo $obra['img']; ?>" alt="Imagem da obra" id="imagem-atual">
          </div>
        </div>
      </div>

      <div class="botoes-acoes">
        <button type="submit" class="btn-salvar">
          <i class="fas fa-save"></i> SALVAR ALTERAÇÕES
        </button>

        <button type="button" class="btn-excluir" onclick="confirmarExclusao(<?php echo $obraId; ?>)">
          <i class="fas fa-trash"></i> EXCLUIR OBRA
        </button>

        <button type="button" class="btn-cancelar" onclick="window.location.href='artistasobra.php'">
          <i class="fas fa-times"></i> CANCELAR
        </button>
      </div>
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
    // Dados das obras para JavaScript
    const obras = <?php echo json_encode($produtos); ?>;

    function carregarObra(obraId) {
      // Redirecionar para a página de edição da obra selecionada
      window.location.href = 'editar_obra.php?id=' + obraId;
    }

    function previewImage(input) {
      const preview = document.getElementById('imagem-atual');
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
      }
    }

    function confirmarExclusao(obraId) {
      Swal.fire({
        title: 'Tem certeza?',
        text: "Esta ação não pode ser desfeita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          // Em um sistema real, aqui faria uma requisição para excluir do banco
          Swal.fire(
            'Excluída!',
            'A obra foi excluída com sucesso.',
            'success'
          ).then(() => {
            window.location.href = 'artistasobra.php';
          });
        }
      });
    }

    // Upload de imagem
    const uploadArea = document.getElementById('upload-area');
    const imageInput = document.getElementById('nova-imagem');

    uploadArea.addEventListener('click', () => imageInput.click());

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