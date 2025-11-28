<?php
require_once 'config.php';

// Verificar se está logado (como artista ou usuário)
if (!isArtista() && !isUsuario()) {
    header("Location: login.php");
    exit();
}

$usuario = getUsuarioLogado($conn);

// Se não encontrou o usuário
if (!$usuario) {
    $usuario = [
        'id' => 0,
        'nome' => 'Usuário',
        'email' => 'usuario@example.com',
        'telefone' => '',
        'endereco' => '',
        'foto_perfil' => '',
        'data_cadastro' => date('Y-m-d H:i:s'),
        'senha' => '',
        'tipo' => 'usuario'
    ];
}

// Determinar qual tabela usar baseado no tipo
$tabela = ($usuario['tipo'] === 'artista') ? 'artistas' : 'usuarios';
$campo_foto = ($usuario['tipo'] === 'artista') ? 'imagem_perfil' : 'foto_perfil';

// PROCESSAR ATUALIZAÇÃO DO PERFIL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["atualizar_perfil"])) {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"] ?? '';
    $endereco = $_POST["endereco"] ?? '';

    // Upload da foto
    $foto_atual = $usuario[$campo_foto] ?? '';

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        if ($usuario['tipo'] === 'artista') {
            // Upload para artista
            $upload_dir = __DIR__ . "/../img/artistas/";
            $new_filename = 'artista_' . $usuario['id'] . '_' . time() . '.' . strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $caminhoBanco = 'img/artistas/' . $new_filename;
        } else {
            // Upload para usuário comum
            $upload_dir = __DIR__ . "/../uploads/usuarios/";
            $new_filename = 'user_' . $usuario['id'] . '_' . time() . '.' . strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $caminhoBanco = $new_filename;
        }

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $upload_path)) {
                // Deletar antiga
                if (!empty($foto_atual)) {
                    $old_path = $upload_dir . basename($foto_atual);
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }

                $foto_atual = $caminhoBanco;
            }
        }
    }

    // Verificar email duplicado
    $sql_check_email = "SELECT id FROM $tabela WHERE email = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check_email);
    $uid = $usuario["id"];
    $stmt_check->bind_param("si", $email, $uid);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $error_message = "Este email já está em uso por outro usuário.";
    } else {
        // Atualizar dados (campos diferentes para artista e usuário)
        if ($usuario['tipo'] === 'artista') {
            $sql_update = "UPDATE artistas SET nome = ?, email = ?, telefone = ?, $campo_foto = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssi", $nome, $email, $telefone, $foto_atual, $usuario['id']);
        } else {
            $sql_update = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?, endereco = ?, $campo_foto = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssssi", $nome, $email, $telefone, $endereco, $foto_atual, $usuario['id']);
        }

        if ($stmt_update->execute()) {
            // Atualizar sessão
            if ($usuario['tipo'] === 'artista') {
                $_SESSION["artistas"]["nome"] = $nome;
                $_SESSION["artistas"]["email"] = $email;
                $_SESSION["artistas"][$campo_foto] = $foto_atual;
            } else {
                $_SESSION["usuario"] = $nome;
            }
            
            $success_message = "Perfil atualizado com sucesso!";
            
            // Atualizar dados locais
            $usuario['nome'] = $nome;
            $usuario['email'] = $email;
            $usuario['telefone'] = $telefone;
            if ($usuario['tipo'] === 'usuario') {
                $usuario['endereco'] = $endereco;
            }
            $usuario[$campo_foto] = $foto_atual;
        } else {
            $error_message = "Erro ao atualizar perfil: " . $stmt_update->error;
        }
        
        $stmt_update->close();
    }
    
    $stmt_check->close();
}
// Buscar mensagens recebidas
$sql_mensagens = "
    SELECT * FROM mensagens_artistas 
    WHERE artista_id = ? 
    ORDER BY data_envio DESC
";
$stmt_mensagens = $conn->prepare($sql_mensagens);
$stmt_mensagens->bind_param("i", $artista_id);
$stmt_mensagens->execute();
$result_mensagens = $stmt_mensagens->get_result();
$mensagens = [];

while ($mensagem = $result_mensagens->fetch_assoc()) {
    $mensagens[] = $mensagem;
}

// Contar mensagens não lidas
$sql_nao_lidas = "SELECT COUNT(*) as total FROM mensagens_artistas WHERE artista_id = ? AND lida = 0";
$stmt_nao_lidas = $conn->prepare($sql_nao_lidas);
$stmt_nao_lidas->bind_param("i", $artista_id);
$stmt_nao_lidas->execute();
$result_nao_lidas = $stmt_nao_lidas->get_result();
$total_nao_lidas = $result_nao_lidas->fetch_assoc()['total'];

// Marcar mensagens como lidas quando acessadas
if (isset($_GET['ler_mensagens'])) {
    $sql_marcar_lidas = "UPDATE mensagens_artistas SET lida = 1 WHERE artista_id = ? AND lida = 0";
    $stmt_marcar = $conn->prepare($sql_marcar_lidas);
    $stmt_marcar->bind_param("i", $artista_id);
    $stmt_marcar->execute();
    $total_nao_lidas = 0;
}
// ALTERAR SENHA (apenas para usuários comuns)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["alterar_senha"]) && $usuario['tipo'] === 'usuario') {
    $senha_atual = $_POST["senha_atual"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (!empty($usuario['senha'])) {
        if (password_verify($senha_atual, $usuario['senha'])) {
            if ($nova_senha === $confirmar_senha) {
                if (strlen($nova_senha) >= 6) {
                    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                    $sql_senha = "UPDATE usuarios SET senha = ? WHERE id = ?";
                    $stmt_senha = $conn->prepare($sql_senha);
                    $uid = $usuario['id'];
                    $stmt_senha->bind_param("si", $nova_senha_hash, $uid);

                    if ($stmt_senha->execute()) {
                        $success_message_senha = "Senha alterada com sucesso!";
                    } else {
                        $error_message_senha = "Erro ao alterar senha.";
                    }
                    
                    $stmt_senha->close();
                } else {
                    $error_message_senha = "A senha deve ter no mínimo 6 caracteres.";
                }
            } else {
                $error_message_senha = "As novas senhas não coincidem.";
            }
        } else {
            $error_message_senha = "Senha atual incorreta.";
        }
    } else {
        $error_message_senha = "Não foi possível verificar a senha atual.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Verseal</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/style.css">
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

<main class="pagina-perfil">
    <div class="titulo-pagina">
        <h1>Meu Perfil</h1>
        <p>Gerencie suas informações pessoais e preferências</p>
        <?php if ($usuario['tipo'] === 'artista'): ?>
        <div class="badge-artista">
            <i class="fas fa-palette"></i> Conta de Artista
        </div>
        <?php endif; ?>
    </div>

    <div class="container-perfil">
        <div class="menu-lateral">
            <div class="info-usuario">
                <div class="avatar">
                    <?php if (!empty($usuario[$campo_foto])): ?>
                        <img src="../<?php echo htmlspecialchars($usuario[$campo_foto]); ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                <p><?php echo ($usuario['tipo'] === 'artista') ? 'Artista' : 'Membro'; ?> desde <?php echo date('m/Y', strtotime($usuario['data_cadastro'] ?? 'now')); ?></p>
            </div>

            <ul class="menu-links">
                <li><a href="artistaperfil.php" class="ativo"><i class="fas fa-user-circle"></i> Meu Perfil</a></li>
                <?php if ($usuario['tipo'] === 'artista'): ?>
                    <li><a href="artistaperfil.php?aba=mensagens">
                    <i class="fas fa-envelope"></i> Mensagens
                    <?php if ($total_nao_lidas > 0): ?>
                        <span class="mensagens-badge"><?php echo $total_nao_lidas; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="editarbiografia.php"><i class="fas fa-edit"></i> Editar Biografia</a></li>
                <?php endif; ?>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>

        <div class="conteudo-principal">
            <!-- SEÇÃO FOTO DE PERFIL -->
            <div class="secao-perfil">
                <h2><i class="fas fa-camera"></i> Foto de Perfil</h2>
                
                <div class="upload-foto">
                    <div class="foto-preview" onclick="document.getElementById('foto_perfil').click()">
                        <?php if (!empty($usuario[$campo_foto])): ?>
                            <img src="../<?php echo htmlspecialchars($usuario[$campo_foto]); ?>" alt="Foto de perfil" id="foto-preview-img">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #e07b67, #cc624e); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <button type="button" class="btn-upload" onclick="document.getElementById('foto_perfil').click()">
                        <i class="fas fa-upload"></i> Alterar Foto
                    </button>
                    <p style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                        Formatos: JPG, PNG, GIF (Máx. 2MB)
                    </p>
                </div>
            </div>

            <!-- SEÇÃO INFORMAÇÕES PESSOAIS -->
            <div class="secao-perfil">
                <h2><i class="fas fa-user-edit"></i> Informações Pessoais</h2>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="file" id="foto_perfil" name="foto_perfil" class="file-input" accept="image/*" onchange="previewImage(this)">
                    
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" placeholder="(11) 99999-9999">
                    </div>

                    <?php if ($usuario['tipo'] === 'usuario'): ?>
                    <div class="form-group">
                        <label for="endereco">Endereço</label>
                        <textarea id="endereco" name="endereco" class="form-control" rows="3" placeholder="Digite seu endereço completo"><?php echo htmlspecialchars($usuario['endereco'] ?? ''); ?></textarea>
                    </div>
                    <?php endif; ?>

                    <button type="submit" name="atualizar_perfil" class="btn-primary">
                        <i class="fas fa-save"></i> Atualizar Perfil
                    </button>
                </form>
            </div>

            <!-- SEÇÃO ALTERAR SENHA (apenas para usuários comuns) -->
            <?php if ($usuario['tipo'] === 'usuario'): ?>
            <div class="secao-perfil">
                <h2><i class="fas fa-lock"></i> Alterar Senha</h2>
                
                <?php if (isset($success_message_senha)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message_senha; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message_senha)): ?>
                    <div class="alert alert-error">
                        <?php echo $error_message_senha; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="senha_atual">Senha Atual</label>
                        <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" class="form-control" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Nova Senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" name="alterar_senha" class="btn-primary">
                        <i class="fas fa-key"></i> Alterar Senha
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
    <div class="social">
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="#"><i class="fab fa-whatsapp"></i></a>
    </div>
</footer>

<script>
// Seus scripts JavaScript permanecem os mesmos
document.getElementById('telefone')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
    }
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('foto-preview-img');
            if (preview) {
                preview.src = e.target.result;
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('foto_perfil')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.size > 2 * 1024 * 1024) {
        alert('O arquivo é muito grande. Por favor, selecione uma imagem menor que 2MB.');
        e.target.value = '';
    }
});

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
});
</script>
</body>
</html>