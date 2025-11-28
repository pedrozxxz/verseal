<?php
require_once 'config.php';

// Verificar se é um artista logado
if (!isArtista()) {
    header("Location: login.php");
    exit;
}

$dados = getUsuarioLogado($conn);

// Se não encontrar o artista no banco
if (!$dados) {
    $erro = "Artista não encontrado no banco de dados.";
    $dados = [
        'nome' => '',
        'descricao' => '',
        'telefone' => '',
        'email' => '',
        'instagram' => '',
        'imagem_perfil' => '../img/jamile.jpg',
    ];
} else {
    // Corrigir o caminho da imagem se existir
    if (!empty($dados['imagem_perfil'])) {
        // Verificar se o caminho já inclui '../'
        if (strpos($dados['imagem_perfil'], '../') !== 0 && strpos($dados['imagem_perfil'], 'http') !== 0) {
            $dados['imagem_perfil'] = '../' . $dados['imagem_perfil'];
        }
        
        // Verificar se o arquivo realmente existe
        $caminhoVerificacao = str_replace('../', '', $dados['imagem_perfil']);
        if (!file_exists($caminhoVerificacao) && !file_exists($dados['imagem_perfil'])) {
            $dados['imagem_perfil'] = '../img/jamile.jpg';
        }
    } else {
        $dados['imagem_perfil'] = '../img/jamile.jpg';
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao_curta = $_POST['descricao'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $social = $_POST['social'];

    // Atualizar dados no banco
    $sql_update = "UPDATE artistas 
        SET nome = ?, descricao = ?, telefone = ?, email = ?, instagram = ?
        WHERE id = ?";

    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssi",
        $nome,
        $descricao_curta,
        $telefone,
        $email,
        $social,
        $dados['id']
    );

    if ($stmt_update->execute()) {
        // PROCESSAR FOTO
        if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($ext, $extensoesPermitidas)) {
                $uploadDir = __DIR__ . '/../img/artistas/';
                
                // Criar diretório se não existir
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Garante nome único e fixo para cada artista
                $arquivoFinal = 'artista_' . $dados['id'] . '.' . $ext;
                $caminhoReal = $uploadDir . $arquivoFinal;
                $caminhoBanco = 'img/artistas/' . $arquivoFinal;

                // Salvar arquivo
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoReal)) {
                    // Atualizar imagem no banco
                    $sql_foto = "UPDATE artistas SET imagem_perfil = ? WHERE id = ?";
                    $stmt_foto = $conn->prepare($sql_foto);
                    $stmt_foto->bind_param("si", $caminhoBanco, $dados['id']);
                    $stmt_foto->execute();
                    $stmt_foto->close();

                    // Atualizar sessão também
                    $_SESSION["artistas"]["imagem_perfil"] = $caminhoBanco;
                }
            }
        }

        // Atualizar sessão com novos dados
        $_SESSION["artistas"]["nome"] = $nome;
        $_SESSION["artistas"]["email"] = $email;

        // Redirecionar com mensagem de sucesso
        header("Location: artistabiografia.php?success=1");
        exit;
    } else {
        $erro = "Erro ao atualizar perfil: " . $stmt_update->error;
    }
    $stmt_update->close();
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

.edit-bio-container .foto-area {
  flex: 1;
  text-align: center;
  position: relative;
}

.edit-bio-container .foto-area img {
  width: 320px;
  height: 320px;
  object-fit: cover;
  border-radius: 20px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  margin-bottom: 15px;
  border: 3px solid #f0dcd0;
  transition: all 0.3s ease;
}

.foto-area:hover img {
  transform: scale(1.02);
  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.file-input-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
  max-width: 250px;
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

.file-input-button {
  display: block;
  padding: 12px 20px;
  background: linear-gradient(135deg, #e07b67, #cc624e);
  color: white;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  box-shadow: 0 4px 15px rgba(204, 98, 78, 0.3);
}

.file-input-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(204, 98, 78, 0.4);
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

.foto-area label {
  display: block;
  margin-bottom: 15px;
  font-weight: 600;
  color: #444;
  font-size: 1.1rem;
}

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
.edit-bio-container textarea:focus {
  border-color: #e07b67;
  box-shadow: 0 0 0 3px rgba(224, 123, 103, 0.1);
}

textarea {
  min-height: 80px;
  resize: vertical;
}

.edit-bio-container .duo {
  display: flex;
  gap: 20px;
}

.edit-bio-container .duo > div {
  flex: 1;
}

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

button:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 25px rgba(204, 98, 78, 0.5);
}

.erro {
  background: #ffe6e6;
  color: #d63031;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #ff7675;
  text-align: center;
}

.sucesso {
  background: #e6f7e6;
  color: #27ae60;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #2ecc71;
  text-align: center;
}

</style>

</head>
<body>
<?php
// header.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Plataforma Artística</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Área do Artista</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/artistahome.css" />
</head>

<body>
<?php
// config.php deve conter apenas configurações, não HTML
require_once 'config.php';
?>
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
        <p>Bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>!</p>
        <small><?php echo $tipoUsuario === 'artista' ? 'Artista' : 'Usuário'; ?></small>
      </div>
      <div class="dropdown-divider"></div>
      <a href="./artistaperfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
      <?php if ($tipoUsuario === 'artista'): ?>
        <a href="./editarbiografia.php" class="dropdown-item"><i class="fas fa-edit"></i> Editar Biografia</a>
      <?php endif; ?>
      <div class="dropdown-divider"></div>
      <a href="./artistalogout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
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
    <img id="preview-img" src="<?php echo htmlspecialchars($dados['imagem_perfil']); ?>" alt="Foto do artista" onerror="this.src='../img/jamile.jpg'">
    
    <label for="foto">Alterar Foto do Perfil</label>
    
    <div class="file-input-wrapper">
      <div class="file-input-button">
        <i class="fas fa-camera"></i> Escolher Nova Foto
      </div>
      <input type="file" name="foto" id="foto" form="form-bio" accept="image/*">
    </div>
    
    <span id="file-name" class="file-name">Nenhum arquivo selecionado</span>
    
    <div class="preview-info">
    </div>
  </div>

  <form id="form-bio" action="" method="post" enctype="multipart/form-data">
    <?php if (isset($erro)): ?>
      <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="sucesso">Perfil atualizado com sucesso!</div>
    <?php endif; ?>

    <label>Nome completo</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($dados['nome']); ?>" required>

    <label>Descrição curta</label>
    <textarea name="descricao" rows="3" required><?php echo htmlspecialchars($dados['descricao']); ?></textarea>

    <label>Telefone</label>
    <input type="tel" name="telefone" value="<?php echo htmlspecialchars($dados['telefone']); ?>">

    <div class="duo">
      <div>
        <label>E-mail</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($dados['email']); ?>" required>
      </div>

      <div>
        <label>Instagram</label>
        <input type="text" name="social" value="<?php echo htmlspecialchars($dados['instagram']); ?>" placeholder="@seuinstagram">
      </div>
    </div>

    <button type="submit">
      <i class="fas fa-save"></i> Salvar Alterações
    </button>
  </form>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');
      if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          profileDropdown.style.display =
            profileDropdown.style.display === 'block' ? 'none' : 'block';
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
    });
document.addEventListener('DOMContentLoaded', function () {
    const inputFile = document.getElementById('foto');
    const imgPreview = document.getElementById('preview-img');
    const fileName = document.getElementById('file-name');
    
    if (inputFile && imgPreview) {
        inputFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Mostrar nome do arquivo
                fileName.textContent = file.name;
                fileName.style.color = '#27ae60';
                
                // Verificar tamanho do arquivo (máximo 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('A imagem deve ter no máximo 5MB');
                    this.value = '';
                    fileName.textContent = 'Arquivo muito grande (máx. 5MB)';
                    fileName.style.color = '#e74c3c';
                    return;
                }
                
                // Verificar tipo do arquivo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Por favor, selecione uma imagem (JPG, PNG ou GIF)');
                    this.value = '';
                    fileName.textContent = 'Tipo de arquivo inválido';
                    fileName.style.color = '#e74c3c';
                    return;
                }
                
                // Criar preview da imagem
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Adicionar efeito de transição suave
                    imgPreview.style.opacity = '0.7';
                    setTimeout(() => {
                        imgPreview.src = e.target.result;
                        imgPreview.style.opacity = '1';
                    }, 200);
                    
                    // Adicionar efeito visual de confirmação
                    imgPreview.style.boxShadow = '0 0 0 3px #27ae60';
                    setTimeout(() => {
                        imgPreview.style.boxShadow = '0 6px 15px rgba(0,0,0,0.2)';
                    }, 1000);
                }
                reader.onerror = function() {
                    alert('Erro ao carregar a imagem. Tente novamente.');
                    this.value = '';
                    fileName.textContent = 'Erro ao carregar arquivo';
                    fileName.style.color = '#e74c3c';
                }
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = 'Nenhum arquivo selecionado';
                fileName.style.color = '#666';
            }
        });
        
        // Efeito hover na área da foto
        const fotoArea = document.querySelector('.foto-area');
        fotoArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '#f9f0ec';
        });
        
        fotoArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '';
        });
        
        fotoArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '';
            if (e.dataTransfer.files.length) {
                inputFile.files = e.dataTransfer.files;
                const event = new Event('change');
                inputFile.dispatchEvent(event);
            }
        });
    }
    
    // Verificar se a imagem atual carregou corretamente
    imgPreview.onerror = function() {
        this.src = '../img/jamile.jpg';
    }
    
    // Verificar se há uma imagem carregada ao iniciar
    if (imgPreview.src && !imgPreview.src.includes('jamile.jpg')) {
        fileName.textContent = 'Imagem atual carregada';
        fileName.style.color = '#27ae60';
    }
});
</script>
</body>
</html>