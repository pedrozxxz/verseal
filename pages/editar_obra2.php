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
<link rel="stylesheet" href="../css/adicionar-obras.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.btn-salvar { background: #cc624e; color: white; border: none; padding:12px 30px; border-radius:8px; cursor:pointer; font-size:1rem; display:inline-flex; align-items:center; gap:8px; transition:background 0.3s; }
.btn-salvar:hover { background:#e07b67; }
.btn-excluir { background:#dc3545; color:white; border:none; padding:12px 30px; border-radius:8px; cursor:pointer; font-size:1rem; display:inline-flex; align-items:center; gap:8px; transition:background 0.3s; }
.btn-excluir:hover { background:#c82333; }
.btn-cancelar { background:#6c757d; color:white; border:none; padding:12px 30px; border-radius:8px; cursor:pointer; font-size:1rem; display:inline-flex; align-items:center; gap:8px; transition:background 0.3s; }
.btn-cancelar:hover { background:#5a6268; }
.botoes-acoes { display:flex; gap:15px; justify-content:center; margin-top:30px; }
.mensagem-erro { background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px; border:1px solid #f5c6cb; }
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

<section class="adicionar-obras">
<div class="container">
<h1>EDITAR OBRAS</h1>

<?php if (isset($erro)): ?>
<div class="mensagem-erro"><i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?></div>
<?php endif; ?>

<form class="form-obras" id="form-obras" method="POST" enctype="multipart/form-data">
<input type="hidden" name="salvar_obra" value="1">

<div class="form-grid">
<div class="form-column">

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
<input type="text" id="nome-obra" name="nome_obra" placeholder="Digite..." value="<?php echo htmlspecialchars($obra['nome']); ?>" required>
</div>

<div class="form-group">
<label for="preco">Preço</label>
<input type="number" id="preco" name="preco" step="0.01" placeholder="Digite..." value="<?php echo $obra['preco']; ?>" required>
</div>

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
<label for="dimensao">Dimensão</label>
<!-- CORREÇÃO: usar $obra['dimensoes'] em vez de $obras['dimensao'] -->
<input type="text" id="dimensao" name="dimensao" placeholder="Digite..." value="<?php echo htmlspecialchars($obra['dimensoes']); ?>" required>
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
<textarea id="descricao" name="descricao" placeholder="Descreva a obra..." rows="4" required><?php echo htmlspecialchars($obra['descricao']); ?></textarea>
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
<img src="<?php echo htmlspecialchars($imagemExibir); ?>" alt="Imagem da obra" id="imagem-atual" onerror="this.src='../img/imagem2.png';">
</div>
</div>
</div>

<div class="botoes-acoes">
<button type="submit" class="btn-salvar"><i class="fas fa-save"></i> SALVAR ALTERAÇÕES</button>
<button type="button" class="btn-excluir" onclick="confirmarExclusao(<?php echo $obraId; ?>)"><i class="fas fa-trash"></i> EXCLUIR OBRA</button>
<button type="button" class="btn-cancelar" onclick="window.location.href='artistasobra.php'"><i class="fas fa-times"></i> CANCELAR</button>
</div>

</form>
</div>
</section>

<footer>
<p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
<div class="social">
<a href="#"><i class="fab fa-instagram"></i></a>
<a href="#"><i class="fab fa-linkedin-in"></i></a>
<a href="#"><i class="fab fa-whatsapp"></i></a>
</div>
</footer>

<script>
function carregarObra(obraId) {
    window.location.href = 'editar_obra2.php?id=' + obraId;
}

function previewImage(input) {
    const preview = document.getElementById('imagem-atual');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) { preview.src = e.target.result; }
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
            const formData = new FormData();
            formData.append('acao', 'excluir');
            formData.append('obra_id', obraId);

            fetch('excluir_obra.php', { method:'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    Swal.fire('Excluída!','A obra foi excluída com sucesso.','success').then(()=>window.location.href='artistasobra.php');
                } else {
                    Swal.fire('Erro!', data.message || 'Erro ao excluir a obra.','error');
                }
            }).catch(()=>Swal.fire('Erro!','Erro de conexão.','error'));
        }
    });
}

// Upload área
const uploadArea = document.getElementById('upload-area');
const imageInput = document.getElementById('nova-imagem');
uploadArea.addEventListener('click', () => imageInput.click());

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
</script>
</body>
</html>