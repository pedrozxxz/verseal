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
        'imagem_perfil' => '',
    ];
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
        if (!empty($_FILES['foto']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $uploadDir = __DIR__ . '/../img/artistas/';

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

        // Atualizar sessão com novos dados
        $_SESSION["artistas"]["nome"] = $nome;
        $_SESSION["artistas"]["email"] = $email;

if ($stmt_update->execute()) {
    // PROCESSAR FOTO (código existente...)
    
    // Atualizar sessão com novos dados
    $_SESSION["artistas"]["nome"] = $nome;
    $_SESSION["artistas"]["email"] = $email;

    // Redirecionar com mensagem de sucesso
    header("Location: artistabiografia.php?success=1");
    exit;
}
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
/* Seus estilos CSS permanecem os mesmos */
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
  </nav>
</header>

<div class="edit-bio-container">
  <div class="foto-area">
    <img src="<?php echo !empty($dados['imagem_perfil']) ? $dados['imagem_perfil'] : '../img/jamile.jpg'; ?>" alt="Foto do artista">
    <input type="file" name="foto" form="form-bio" accept="image/*">
  </div>

  <form id="form-bio" action="" method="post" enctype="multipart/form-data">
    <?php if (isset($erro)): ?>
      <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>

    <label>Nome completo</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($dados['nome']); ?>" required>

    <label>Descrição curta</label>
    <textarea name="descricao" rows="3" required><?php echo htmlspecialchars($dados['descricao']); ?></textarea>

    <label>Telefone</label>
    <input type="tel" name="telefone" value="<?php echo $dados['telefone']; ?>">

    <div class="duo">
      <div>
        <label>E-mail</label>
        <input type="email" name="email" value="<?php echo $dados['email']; ?>" required>
      </div>

      <div>
        <label>Instagram</label>
        <input type="text" name="social" value="<?php echo $dados['instagram']; ?>">
      </div>
    </div>

    <button type="submit">Salvar Alterações</button>
  </form>
</div>

<script>
  // Seus scripts JavaScript permanecem os mesmos
  document.addEventListener('DOMContentLoaded', function () {
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