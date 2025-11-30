<?php
session_start();


// 🔹 INICIALIZAR SISTEMA DE NOTIFICAÇÕES
if (!isset($_SESSION['carrinho_notificacoes'])) {
    $_SESSION['carrinho_notificacoes'] = [];
}

// Verificar se o usuário está logado (cliente)
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Carregar dados do usuário a partir da sessão
$usuarioLogado = $_SESSION["usuario"];
$usuario_id = $usuarioLogado['id'] ?? null;

if (!$usuario_id) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Função auxiliar: buscar usuário nas tabelas clientes e usuarios
function buscar_usuario($conn, $id) {
    // Tentar tabela clientes primeiro
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();
    if ($usuario) {
        $usuario['__table'] = 'usuarios';
        return $usuario;
    }

    // Tentar tabela usuarios
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();
    if ($usuario) {
        $usuario['__table'] = 'usuarios';
        return $usuario;
    }

    return null;
}

$usuario = buscar_usuario($conn, $usuario_id);
if (!$usuario) {
    // usuário não encontrado: logout
    session_destroy();
    header("Location: login.php");
    exit();
}

// Inicializar variáveis (evita warnings e preenche o formulário)
$nome = $usuario['nome'] ?? '';
$email = $usuario['email'] ?? '';
$telefone = $usuario['telefone'] ?? '';
$endereco = $usuario['endereco'] ?? '';
$foto_perfil = $usuario['foto_perfil'] ?? '';
$usuario_table = $usuario['__table'] ?? 'usuarios';

// --- Processar atualização do perfil ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizar_perfil"])) {
    // Receber inputs (sanitize básico)
    $nome = trim($_POST["nome"] ?? $nome);
    $email = trim($_POST["email"] ?? $email);
    $telefone = trim($_POST["telefone"] ?? $telefone);
    $endereco = trim($_POST["endereco"] ?? $endereco);

    // Processar upload de foto (se houver)
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . "/../uploads/usuarios/"; // caminho absoluto
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = $_FILES['foto_perfil']['name'];
        $file_tmp = $_FILES['foto_perfil']['tmp_name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'user_' . $usuario_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // remover anterior apenas se existir e for arquivo válido
                if (!empty($usuario['foto_perfil'])) {
                    $old_path = $upload_dir . $usuario['foto_perfil'];
                    if (file_exists($old_path) && is_file($old_path)) {
                        @unlink($old_path);
                    }
                }
                $foto_perfil = $new_filename;
            } else {
                $error_message = "Falha ao enviar a imagem. Tente novamente.";
            }
        } else {
            $error_message = "Formato de imagem não permitido. Use JPG, PNG ou GIF.";
        }
    }

    // Verificar se email já existe em qualquer tabela (excluindo o usuário atual)
    $email_exists = false;
    // Verificar em usuarios
    $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $email, $usuario_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if ($res_check && $res_check->num_rows > 0) {
        $email_exists = true;
    }
    // Verificar em clientes
    if (!$email_exists) {
        $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $email, $usuario_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if ($res_check && $res_check->num_rows > 0) {
            $email_exists = true;
        }
    }

    if ($email_exists) {
        $error_message = "Este email já está em uso por outro usuário.";
    } else {
        // Preparar atualização dinâmica com base nas colunas existentes na tabela do usuário
        $update_fields = [];
        $update_values = [];
        $update_types = "";

        // Campos básicos que esperamos existir
        $possible_fields = [
            'nome' => ['type' => 's', 'value' => $nome],
            'email' => ['type' => 's', 'value' => $email],
            'telefone' => ['type' => 's', 'value' => $telefone],
            'endereco' => ['type' => 's', 'value' => $endereco],
            'foto_perfil' => ['type' => 's', 'value' => $foto_perfil],
        ];

        // Verificar colunas reais na tabela onde o usuário está armazenado
        foreach ($possible_fields as $col => $meta) {
            $check = $conn->query("SHOW COLUMNS FROM " . $conn->real_escape_string($usuario_table) . " LIKE '" . $conn->real_escape_string($col) . "'");
            if ($check && $check->num_rows > 0) {
                $update_fields[] = "$col = ?";
                $update_values[] = $meta['value'];
                $update_types .= $meta['type'];
            }
        }

        // Adicionar id ao bind
        $update_values[] = $usuario_id;
        $update_types .= "i";

        if (!empty($update_fields)) {
            $sql_update = "UPDATE " . $usuario_table . " SET " . implode(", ", $update_fields) . " WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                // bind_param exige variáveis, então usar call_user_func_array
                $bind_names[] = $update_types;
                for ($i = 0; $i < count($update_values); $i++) {
                    $bind_names[] = &$update_values[$i];
                }
                call_user_func_array([$stmt_update, 'bind_param'], $bind_names);

                if ($stmt_update->execute()) {
                    // Atualizar sessão e $usuario local
                    if ($usuario_table === 'usuarios') {
                        $_SESSION['usuarios']['nome'] = $nome;
                    } else {
                        // se preferir guardar em outra chave, ajuste aqui
                        $_SESSION['usuarios']['nome'] = $nome;
                    }

                    $success_message = "Perfil atualizado com sucesso!";
                    // Recarregar usuario do banco para manter consistência
                    $usuario = buscar_usuario($conn, $usuario_id);
                    $nome = $usuario['nome'] ?? $nome;
                    $email = $usuario['email'] ?? $email;
                    $telefone = $usuario['telefone'] ?? $telefone;
                    $endereco = $usuario['endereco'] ?? $endereco;
                    $foto_perfil = $usuario['foto_perfil'] ?? $foto_perfil;
                } else {
                    $error_message = "Erro ao atualizar perfil: " . $stmt_update->error;
                }
            } else {
                $error_message = "Erro na preparação da atualização: " . $conn->error;
            }
        } else {
            $error_message = "Nenhum campo disponível para atualizar.";
        }
    }
}

// --- Processar alteração de senha ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["alterar_senha"])) {
    $senha_atual = $_POST["senha_atual"] ?? '';
    $nova_senha = $_POST["nova_senha"] ?? '';
    $confirmar_senha = $_POST["confirmar_senha"] ?? '';

    // Verificar se a coluna senha existe e se temos hash para comparar
    if (empty($usuario['senha'])) {
        $error_message_senha = "Não foi possível verificar a senha atual.";
    } else {
        if (password_verify($senha_atual, $usuario['senha'])) {
            if ($nova_senha === $confirmar_senha) {
                if (strlen($nova_senha) >= 6) {
                    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                    // Atualizar senha na tabela correta
                    $sql_senha = "UPDATE " . $usuario_table . " SET senha = ? WHERE id = ?";
                    $stmt_senha = $conn->prepare($sql_senha);
                    if ($stmt_senha) {
                        $stmt_senha->bind_param("si", $nova_senha_hash, $usuario_id);
                        if ($stmt_senha->execute()) {
                            $success_message_senha = "Senha alterada com sucesso!";
                            // Recarregar usuário para atualizar hash local
                            $usuario = buscar_usuario($conn, $usuario_id);
                        } else {
                            $error_message_senha = "Erro ao alterar senha: " . $stmt_senha->error;
                        }
                    } else {
                        $error_message_senha = "Erro na preparação da query de senha: " . $conn->error;
                    }
                } else {
                    $error_message_senha = "A senha deve ter pelo menos 6 caracteres.";
                }
            } else {
                $error_message_senha = "As novas senhas não coincidem.";
            }
        } else {
            $error_message_senha = "Senha atual incorreta.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meu Perfil - Verseal</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/perfil.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* estilos (mantive os seus) */
.notificacao-carrinho { position: relative; display: inline-block; }
.carrinho-badge { position: absolute; top: -8px; right: -8px; background: #e74c3c; color: white; border-radius: 50%; padding: 4px 8px; font-size: 0.7rem; min-width: 18px; height: 18px; text-align: center; line-height: 1; font-weight: bold; animation: pulse 2s infinite; display: none; align-items: center; justify-content: center; transition: all 0.3s ease; }
@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
.badge-bounce { animation: bounce 0.5s ease; }
@keyframes bounce { 0%, 20%, 60%, 100% { transform: translateY(0); } 40% { transform: translateY(-10px); } 80% { transform: translateY(-5px); } }
.foto-preview { position: relative; width: 150px; height: 150px; border-radius: 50%; overflow: hidden; cursor: pointer; border: 3px solid #e07b67; transition: all 0.3s ease; margin: 0 auto 15px; }
.foto-preview:hover { border-color: #cc624e; transform: scale(1.05); }
.foto-preview img { width: 100%; height: 100%; object-fit: cover; }
.foto-preview .overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; color: white; font-size: 1.5rem; }
.foto-preview:hover .overlay { opacity: 1; }
.btn-upload { background: #e07b67; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background 0.3s ease; display: block; margin: 0 auto; }
.btn-upload:hover { background: #cc624e; }
.file-input { display: none; }
</style>
</head>
<body>
<header>
  <div class="logo">Verseal</div>
  <nav>
    <a href="../index.php">Início</a>
    <a href="./produto.php">Obras</a>
    <a href="./sobre.php">Sobre</a>
    <a href="./artistas.php">Artistas</a>
    <a href="./contato.php">Contato</a>

    <div class="notificacao-carrinho">
        <a href="./carrinho.php" class="icon-link">
            <i class="fas fa-shopping-cart"></i>
            <span class="carrinho-badge" id="carrinhoBadge">
                <?php
                $total_notificacoes = count($_SESSION['carrinho_notificacoes'] ?? []);
                if ($total_notificacoes > 0) {
                    echo $total_notificacoes;
                }
                ?>
            </span>
        </a>
    </div>

    <div class="profile-dropdown">
      <a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
      <div class="dropdown-content" id="profile-dropdown">
        <?php if (isset($usuarioLogado)): ?>
          <div class="user-info">
            <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>!</p>
          </div>
          <div class="dropdown-divider"></div>
          <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
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

<main class="pagina-perfil">
    <div class="titulo-pagina">
        <h1>Meu Perfil</h1>
        <p>Gerencie suas informações pessoais e preferências</p>
    </div>

    <div class="container-perfil">
        <div class="menu-lateral">
            <div class="info-usuario">
                <div class="avatar">
                    <?php if (!empty($foto_perfil)): ?>
                        <img src="../uploads/usuarios/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" id="avatar-img" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #e07b67, #cc624e); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h3 id="user-name-sidebar"><?php echo htmlspecialchars($nome); ?></h3>
                <p>Membro desde <?php echo date('m/Y', strtotime($usuario['data_cadastro'] ?? 'now')); ?></p>
            </div>

            <ul class="menu-links">
                <li><a href="perfil.php" class="ativo"><i class="fas fa-user-circle"></i> Meu Perfil</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>

        <div class="conteudo-principal">
            <div class="secao-perfil">
                <h2><i class="fas fa-camera"></i> Foto de Perfil</h2>

                <div class="upload-foto">
                    <div class="foto-preview" onclick="document.getElementById('foto_perfil').click()">
                        <?php if (!empty($foto_perfil)): ?>
                            <img src="../uploads/usuarios/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" id="foto-preview-img">
                        <?php else: ?>
                            <div id="default-avatar" style="width: 100%; height: 100%; background: linear-gradient(135deg, #e07b67, #cc624e); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="overlay"><i class="fas fa-camera"></i></div>
                    </div>
                    <button type="button" class="btn-upload" onclick="document.getElementById('foto_perfil').click()">
                        <i class="fas fa-upload"></i> Alterar Foto
                    </button>
                    <p style="font-size: 0.8rem; color: #666; margin-top: 5px;">Formatos: JPG, PNG, GIF (Máx. 2MB)</p>
                </div>
            </div>

            <div class="secao-perfil">
                <h2><i class="fas fa-user-edit"></i> Informações Pessoais</h2>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" id="form-perfil">
                    <input type="file" id="foto_perfil" name="foto_perfil" class="file-input" accept="image/*" onchange="previewImage(this)">

                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" class="form-control" value="<?php echo htmlspecialchars($nome); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" class="form-control" value="<?php echo htmlspecialchars($telefone); ?>" placeholder="(11) 99999-9999">
                    </div>

                    <div class="form-group">
                        <label for="endereco">Endereço</label>
                        <textarea id="endereco" name="endereco" class="form-control" rows="3" placeholder="Digite seu endereço completo"><?php echo htmlspecialchars($endereco); ?></textarea>
                    </div>

                    <button type="submit" name="atualizar_perfil" class="btn-primary"><i class="fas fa-save"></i> Atualizar Perfil</button>
                </form>
            </div>

            <div class="secao-perfil">
                <h2><i class="fas fa-lock"></i> Alterar Senha</h2>

                <?php if (isset($success_message_senha)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message_senha); ?></div>
                <?php endif; ?>
                <?php if (isset($error_message_senha)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message_senha); ?></div>
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

                    <button type="submit" name="alterar_senha" class="btn-primary"><i class="fas fa-key"></i> Alterar Senha</button>
                </form>
            </div>
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
// Sistema de notificações
function atualizarBadgeCarrinho() {
    const badge = document.getElementById('carrinhoBadge');
    const totalNotificacoes = <?php echo count($_SESSION['carrinho_notificacoes'] ?? []); ?>;
    if (totalNotificacoes > 0) {
        badge.textContent = totalNotificacoes;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    atualizarBadgeCarrinho();
});

// Preview da imagem em tempo real
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('foto-preview-img');
            const defaultAvatar = document.getElementById('default-avatar');

            if (previewImg) {
                previewImg.src = e.target.result;
            } else {
                if (defaultAvatar) defaultAvatar.style.display = 'none';
                const fotoPreview = document.querySelector('.foto-preview');
                if (fotoPreview) {
                    const newImg = document.createElement('img');
                    newImg.id = 'foto-preview-img';
                    newImg.src = e.target.result;
                    newImg.alt = 'Preview da foto';
                    newImg.style.width = '100%';
                    newImg.style.height = '100%';
                    newImg.style.objectFit = 'cover';
                    fotoPreview.insertBefore(newImg, fotoPreview.firstChild);
                }
            }

            const avatarImg = document.getElementById('avatar-img');
            if (avatarImg) {
                avatarImg.src = e.target.result;
            } else {
                const avatarDiv = document.querySelector('.avatar');
                if (avatarDiv) {
                    const newAvatarImg = document.createElement('img');
                    newAvatarImg.id = 'avatar-img';
                    newAvatarImg.src = e.target.result;
                    newAvatarImg.alt = 'Foto de perfil';
                    newAvatarImg.style.width = '100%';
                    newAvatarImg.style.height = '100%';
                    newAvatarImg.style.objectFit = 'cover';
                    newAvatarImg.style.borderRadius = '50%';
                    while (avatarDiv.firstChild) avatarDiv.removeChild(avatarDiv.firstChild);
                    avatarDiv.appendChild(newAvatarImg);
                }
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Validação do tamanho e tipo do arquivo (reativar botão e input)
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('foto_perfil');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024;
                if (fileSize > 2) {
                    alert('O arquivo é muito grande. Por favor, selecione uma imagem de até 2MB.');
                    this.value = '';
                    return false;
                }
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Por favor, selecione apenas imagens nos formatos JPG, PNG ou GIF.');
                    this.value = '';
                    return false;
                }
            }
        });
    }

    // Dropdown do perfil
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        document.addEventListener('click', function (e) {
            if (!profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
        profileDropdown.addEventListener('click', function (e) { e.stopPropagation(); });
    }
});
</script>
</body>
</html>
