<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["usuario"]) || !is_array($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$usuarioLogado = $_SESSION["usuario"];
$nomeUsuario = $usuarioLogado['nome'] ?? '';

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obter ID da obra da URL
$obraId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Buscar obra do banco de dados
$sql = "SELECT * FROM produtos WHERE id = ? AND artista = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $obraId, $nomeUsuario);
$stmt->execute();
$result = $stmt->get_result();
$obra = $result->fetch_assoc();

if (!$obra) {
    die("Obra não encontrada ou você não tem permissão para editá-la.");
}

// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_obra'])) {
    $nome = $_POST['nome_obra'] ?? '';
    $preco = floatval($_POST['preco'] ?? 0);
    $tecnica = $_POST['tecnica'] ?? '';
    $dimensoes = $_POST['dimensao'] ?? ''; // CORREÇÃO: usar dimensoes
    $ano = intval($_POST['ano'] ?? 0);
    $material = $_POST['material'] ?? '';
    $descricao = $_POST['descricao'] ?? '';

    // Upload de imagem
    $imagem_url = $obra['imagem_url'] ?? '';
    if (isset($_FILES['nova_imagem']) && $_FILES['nova_imagem']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../img/obras/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['nova_imagem']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['nova_imagem']['tmp_name'], $file_path)) {
            $imagem_url = "img/obras/" . $file_name;
        }
    }

    // CORREÇÃO: Atualizar no banco com coluna dimensoes
    $sql_update = "UPDATE produtos SET nome = ?, preco = ?, descricao = ?, dimensoes = ?, tecnica = ?, ano = ?, material = ?, imagem_url = ? WHERE id = ? AND artista = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sdsssisssi", $nome, $preco, $descricao, $dimensoes, $tecnica, $ano, $material, $imagem_url, $obraId, $nomeUsuario);

    if ($stmt_update->execute()) {
        header('Location: artistasobra.php?obra_editada=' . $obraId . '&ordenacao=recentes');
        exit();
    } else {
        $erro = "Erro ao atualizar a obra: " . $stmt_update->error;
    }
}

// Buscar todas as obras do artista para o select
$sql_obras = "SELECT id, nome FROM produtos WHERE artista = ? ORDER BY nome";
$stmt_obras = $conn->prepare($sql_obras);
$stmt_obras->bind_param("s", $nomeUsuario);
$stmt_obras->execute();
$result_obras = $stmt_obras->get_result();
$todasObras = [];
while ($row = $result_obras->fetch_assoc()) {
    $todasObras[$row['id']] = $row;
}

// Definir imagem a exibir
$imagemExibir = '../img/imagem2.png'; // CORREÇÃO: imagem padrão correta
if (isset($obra['imagem_url']) && !empty($obra['imagem_url'])) {
    $imagemExibir = '../' . $obra['imagem_url'];
}
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
  content: 'EDITAR OBRA';
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

/* BOTÕES DE AÇÃO */
.botoes-acoes {
  display: flex;
  gap: 20px;
  justify-content: center;
  align-items: center;
  margin-top: 40px;
  padding: 20px 0;
  flex-wrap: wrap;
  grid-column: 1 / -1;
}

.btn-base {
  border: none;
  padding: 15px 30px;
  border-radius: 30px;
  color: #fff;
  font-size: 1.1rem;
  cursor: pointer;
  width: 100%;
  max-width: 300px;
  transition: all 0.3s ease;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  text-decoration: none;
  font-family: 'Open Sans', sans-serif;
}

.btn-salvar {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  box-shadow: 0 8px 20px rgba(204, 98, 78, .4);
}

.btn-salvar:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 25px rgba(204, 98, 78, .5);
}

.btn-excluir {
  background: linear-gradient(135deg, #dc3545, #c82333);
  box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
}

.btn-excluir:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 25px rgba(220, 53, 69, 0.5);
}

.btn-cancelar {
  background: linear-gradient(135deg, #6c757d, #5a6268);
  box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
}

.btn-cancelar:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 25px rgba(108, 117, 125, 0.5);
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

/* MENSAGEM DE ERRO */
.mensagem-erro {
  background: #f8d7da;
  color: #721c24;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid #f5c6cb;
  display: flex;
  align-items: center;
  gap: 10px;
  grid-column: 1 / -1;
}

.mensagem-erro i {
  font-size: 1.2rem;
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
  
  .botoes-acoes {
    flex-direction: column;
  }
  
  .btn-base {
    max-width: 100%;
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

/* ESTILOS ESPECÍFICOS PARA O SELECT DE OBRA */
.form-group select {
  width: 100%;
  padding: 13px 15px;
  border: 2px solid #f0dcd0;
  background: #fdf9f8;
  border-radius: 12px;
  font-size: 1rem;
  transition: .3s;
  font-family: 'Open Sans', sans-serif;
  color: #333;
  cursor: pointer;
}

.form-group select:focus {
  border-color: #e07b67;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(224, 123, 103, .15);
  outline: none;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
  font-family: 'Open Sans', sans-serif;
  font-size: 0.95rem;
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
<a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
<div class="dropdown-content" id="profile-dropdown">
<?php if (!empty($nomeUsuario)): ?>
<div class="user-info"><p>Seja bem-vindo, <?php echo htmlspecialchars($nomeUsuario); ?>!</p></div>
<div class="dropdown-divider"></div>
<a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
<a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
<?php else: ?>
<div class="user-info"><p>Faça login para acessar seu perfil</p></div>
<div class="dropdown-divider"></div>
<a href="login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
<a href="login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
<?php endif; ?>
</div>
</div>
</nav>
</header>

<!-- CONTAINER PRINCIPAL COM O MESMO LAYOUT -->
<div class="edit-obra-container">
  <!-- FORMULÁRIO À ESQUERDA -->
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="salvar_obra" value="1">

    <?php if (isset($erro)): ?>
    <div class="mensagem-erro">
      <i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?>
    </div>
    <?php endif; ?>

    <div class="form-group">
      <label for="select-obra">Selecione a Obra</label>
      <select id="select-obra" name="obra_id" onchange="carregarObra(this.value)">
        <?php foreach ($todasObras as $id => $obra_item): ?>
        <option value="<?php echo $id; ?>" <?php echo $id == $obraId ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($obra_item['nome']); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="nome-obra">Nome da Obra</label>
      <input type="text" id="nome-obra" name="nome_obra" placeholder="Digite o nome da obra..." value="<?php echo htmlspecialchars($obra['nome']); ?>" required>
    </div>

    <div class="form-group">
      <label for="preco">Preço (R$)</label>
      <input type="number" id="preco" name="preco" step="0.01" placeholder="0.00" value="<?php echo $obra['preco']; ?>" required>
    </div>

    <div class="form-group">
      <label for="descricao">Descrição da Obra</label>
      <textarea id="descricao" name="descricao" placeholder="Descreva a obra..." required><?php echo htmlspecialchars($obra['descricao']); ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="tecnica">Técnica/Estilo</label>
        <select id="tecnica" name="tecnica" required>
          <?php
          $tecnicas = ["Técnica mista","Pintura digital","Expressionismo","Abstração","Figurativo","Realismo","Urban sketching","Manual","NFT","Mesa Digital","Pintura","Escultura","Fotografia"];
          foreach($tecnicas as $t): ?>
          <option value="<?php echo $t; ?>" <?php echo $obra['tecnica']==$t?'selected':''; ?>><?php echo $t; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="dimensao">Dimensões</label>
        <input type="text" id="dimensao" name="dimensao" placeholder="Ex: 50x70cm" value="<?php echo htmlspecialchars($obra['dimensoes']); ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="ano">Ano de Criação</label>
        <input type="number" id="ano" name="ano" min="1900" max="2030" value="<?php echo $obra['ano']; ?>" required>
      </div>

      <div class="form-group">
        <label for="material">Material</label>
        <input type="text" id="material" name="material" placeholder="Ex: Tinta acrílica, Tela..." value="<?php echo htmlspecialchars($obra['material']); ?>" required>
      </div>
    </div>

    <!-- INPUT DO FILE ESCONDIDO DENTRO DO FORMULÁRIO -->
    <input type="file" id="nova-imagem" name="nova_imagem" accept="image/*" style="display: none;">

    <div class="botoes-acoes">
      <button type="submit" class="btn-base btn-salvar">
        <i class="fas fa-save"></i> SALVAR ALTERAÇÕES
      </button>
      <button type="button" class="btn-base btn-excluir" onclick="confirmarExclusao(<?php echo $obraId; ?>)">
        <i class="fas fa-trash"></i> EXCLUIR OBRA
      </button>
      <a href="artistasobra.php" class="btn-base btn-cancelar">
        <i class="fas fa-times"></i> CANCELAR
      </a>
    </div>
  </form>

  <!-- ÁREA DA IMAGEM À DIREITA -->
  <div class="foto-area">
    <div id="previewContainer">
      <img src="<?php echo htmlspecialchars($imagemExibir); ?>" alt="Imagem da obra" id="imagem-atual" onerror="this.src='../img/imagem2.png';">
    </div>
    
    <!-- BOTÃO PARA ACIONAR O INPUT FILE -->
    <div class="file-input-wrapper">
      <div class="file-input-button" onclick="document.getElementById('nova-imagem').click()">
        <i class="fas fa-upload"></i> ALTERAR IMAGEM
      </div>
    </div>
    <span id="file-name" class="file-name">Clique para alterar a imagem atual</span>
    <small style="color: #666; display: block; margin-top: 8px;">Formatos: JPG, PNG, GIF (Máx. 10MB)</small>
  </div>
</div>

<script>
function carregarObra(obraId) {
    window.location.href = 'editar_obra2.php?id=' + obraId;
}

function previewImage(input) {
    const preview = document.getElementById('imagem-atual');
    const fileName = document.getElementById('file-name');
    
    if (input.files && input.files[0]) {
        // Verificar tamanho do arquivo (máximo 10MB)
        if (input.files[0].size > 10 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'A imagem deve ter no máximo 10MB',
                confirmButtonText: 'OK'
            });
            input.value = '';
            fileName.textContent = 'Arquivo muito grande (máx. 10MB)';
            fileName.style.color = '#e74c3c';
            return;
        }
        
        fileName.textContent = input.files[0].name;
        fileName.style.color = '#27ae60';
        
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
            // Mostrar loading
            Swal.fire({
                title: 'Excluindo...',
                text: 'Por favor, aguarde',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('acao', 'excluir');
            formData.append('obra_id', obraId);

            fetch('excluir_obra.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Erro na rede');
                }
                return res.json();
            })
            .then(data => {
                if(data.success){
                    Swal.fire({
                        title: 'Excluída!',
                        text: 'A obra foi excluída com sucesso.',
                        icon: 'success',
                        confirmButtonColor: '#cc624e',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'artistasobra.php';
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: data.message || 'Erro ao excluir a obra.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Entendi'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    title: 'Erro!',
                    text: 'Erro de conexão. Verifique sua internet.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Entendi'
                });
            });
        }
    });
}

// Preview da imagem quando selecionar nova
document.getElementById('nova-imagem').addEventListener('change', function(e) {
    previewImage(this);
});

// Dropdown perfil
const profileIcon = document.getElementById('profile-icon');
const profileDropdown = document.getElementById('profile-dropdown');
if(profileIcon && profileDropdown){
    profileIcon.addEventListener('click', e=>{
        e.preventDefault();
        profileDropdown.style.display = profileDropdown.style.display==='block'?'none':'block';
    });
    document.addEventListener('click', e=>{
        if(!profileDropdown.contains(e.target) && e.target!==profileIcon){
            profileDropdown.style.display='none';
        }
    });
}

// Validação do formulário
document.querySelector('form').addEventListener('submit', function(e) {
    const preco = document.getElementById('preco').value;
    const ano = document.getElementById('ano').value;
    
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
});
</script>
</body>
</html>