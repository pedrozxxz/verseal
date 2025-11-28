<?php
session_start();

// 🔹 INICIALIZAR SISTEMA DE NOTIFICAÇÕES
if (!isset($_SESSION['carrinho_notificacoes'])) {
    $_SESSION['carrinho_notificacoes'] = [];
}

// Verificar se o usuário está logado - CORREÇÃO AQUI
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

// CORREÇÃO: Verificar se $_SESSION["usuario"] é array ou string
if (is_array($_SESSION["usuario"])) {
    $usuario_nome = $_SESSION["usuario"]['nome'];
    $usuario_id = $_SESSION["usuario"]['id'] ?? null;
} else {
    // Se for string, usar como nome
    $usuario_nome = $_SESSION["usuario"];
    $usuario_id = null;
}

// Buscar dados do usuário - CORREÇÃO AQUI
$sql = "SELECT * FROM usuarios WHERE nome = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario_nome);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Se não encontrou pelo nome e temos ID, tentar pelo ID
if (!$usuario && $usuario_id) {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
}

// Se ainda não encontrou, tentar buscar o primeiro usuário com esse nome
if (!$usuario) {
    $sql = "SELECT * FROM usuarios WHERE nome = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario_nome);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
}

// Se ainda não encontrou nenhum usuário, fazer logout
if (!$usuario) {
    session_destroy();
    header("Location: login.php");
    exit();
}


// Processar atualização do perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["atualizar_perfil"])) {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"] ?? '';
    $endereco = $_POST["endereco"] ?? '';
    
    // Processar upload de foto
    $foto_perfil = $usuario['foto_perfil'] ?? ''; // Manter foto atual se não fizer upload
    
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/usuarios/";
        
        // Criar diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $new_filename = 'user_' . $usuario['id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $upload_path)) {
                $foto_perfil = $new_filename;
                
                // Remover foto anterior se existir
                if (!empty($usuario['foto_perfil']) && file_exists($upload_dir . $usuario['foto_perfil'])) {
                    unlink($upload_dir . $usuario['foto_perfil']);
                }
            }
        }
    }
    
    // Verificar se email já existe (excluindo o usuário atual)
    $sql_check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check_email);
    $stmt_check->bind_param("si", $email, $usuario['id']);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $error_message = "Este email já está em uso por outro usuário.";
    } else {
        // Construir a query dinamicamente baseado nos campos disponíveis
        $update_fields = [];
        $update_values = [];
        $update_types = "";
        
        $update_fields[] = "nome = ?";
        $update_values[] = $nome;
        $update_types .= "s";
        
        $update_fields[] = "email = ?";
        $update_values[] = $email;
        $update_types .= "s";
        
        // Verificar se a coluna telefone existe antes de adicionar
        $check_telefone = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'telefone'");
        if ($check_telefone->num_rows > 0) {
            $update_fields[] = "telefone = ?";
            $update_values[] = $telefone;
            $update_types .= "s";
        }
        
        // Verificar se a coluna endereco existe antes de adicionar
        $check_endereco = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'endereco'");
        if ($check_endereco->num_rows > 0) {
            $update_fields[] = "endereco = ?";
            $update_values[] = $endereco;
            $update_types .= "s";
        }
        
        // Verificar se a coluna foto_perfil existe antes de adicionar
        $check_foto_perfil = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
        if ($check_foto_perfil->num_rows > 0) {
            $update_fields[] = "foto_perfil = ?";
            $update_values[] = $foto_perfil;
            $update_types .= "s";
        }
        
        $update_values[] = $usuario['id'];
        $update_types .= "i";
        
        $sql_update = "UPDATE usuarios SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param($update_types, ...$update_values);
        
        if ($stmt_update->execute()) {
            $_SESSION["usuario"] = $nome;
            $success_message = "Perfil atualizado com sucesso!";
            // Atualizar dados locais
            $usuario['nome'] = $nome;
            $usuario['email'] = $email;
            $usuario['telefone'] = $telefone;
            $usuario['endereco'] = $endereco;
            $usuario['foto_perfil'] = $foto_perfil;
        } else {
            $error_message = "Erro ao atualizar perfil: " . $conn->error;
        }
    }
}

// Processar alteração de senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["alterar_senha"])) {
    $senha_atual = $_POST["senha_atual"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST["confirmar_senha"];
    
    // Verificar senha atual
    if (password_verify($senha_atual, $usuario['senha'])) {
        if ($nova_senha === $confirmar_senha) {
            if (strlen($nova_senha) >= 6) {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql_senha = "UPDATE usuarios SET senha = ? WHERE id = ?";
                $stmt_senha = $conn->prepare($sql_senha);
                $stmt_senha->bind_param("si", $nova_senha_hash, $usuario['id']);
                
                if ($stmt_senha->execute()) {
                    $success_message_senha = "Senha alterada com sucesso!";
                } else {
                    $error_message_senha = "Erro ao alterar senha: " . $conn->error;
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Verseal</title>
   <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/perfil.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* 🔹 SISTEMA DE NOTIFICAÇÕES - IGUAL À PÁGINA SOBRE */
    .notificacao-carrinho {
        position: relative;
        display: inline-block;
    }

    .carrinho-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        padding: 4px 8px;
        font-size: 0.7rem;
        min-width: 18px;
        height: 18px;
        text-align: center;
        line-height: 1;
        font-weight: bold;
        animation: pulse 2s infinite;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .badge-bounce {
        animation: bounce 0.5s ease;
    }

    @keyframes bounce {
        0%, 20%, 60%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        80% { transform: translateY(-5px); }
    }

    /* 🔹 ESTILOS PARA PREVIEW DA FOTO */
    .foto-preview {
        position: relative;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        cursor: pointer;
        border: 3px solid #e07b67;
        transition: all 0.3s ease;
        margin: 0 auto 15px;
    }

    .foto-preview:hover {
        border-color: #cc624e;
        transform: scale(1.05);
    }

    .foto-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .foto-preview .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: white;
        font-size: 1.5rem;
    }

    .foto-preview:hover .overlay {
        opacity: 1;
    }

    .btn-upload {
        background: #e07b67;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
        display: block;
        margin: 0 auto;
    }

    .btn-upload:hover {
        background: #cc624e;
    }

    .file-input {
        display: none;
    }
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
    
    <!-- 🔹 ÍCONE DO CARRINHO COM NOTIFICAÇÃO -->
    <div class="notificacao-carrinho">
        <a href="./carrinho.php" class="icon-link">
            <i class="fas fa-shopping-cart"></i>
            <span class="carrinho-badge" id="carrinhoBadge">
                <?php 
                $total_notificacoes = count($_SESSION['carrinho_notificacoes']);
                if ($total_notificacoes > 0) {
                    echo $total_notificacoes;
                }
                ?>
            </span>
        </a>
    </div>
    
    <div class="profile-dropdown">
  <a href="perfil.php" class="icon-link" id="profile-icon">
    <i class="fas fa-user"></i>
  </a>
  </a>
  <div class="dropdown-content" id="profile-dropdown">
    <?php if (isset($usuario) && !empty($usuario['nome'])): ?>
      <div class="user-info">
        <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuario['nome']); ?></span>!</p>
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

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="pagina-perfil">
        <div class="titulo-pagina">
            <h1>Meu Perfil</h1>
            <p>Gerencie suas informações pessoais e preferências</p>
        </div>

        <div class="container-perfil">
            <!-- MENU LATERAL -->
            <div class="menu-lateral">
                <div class="info-usuario">
                    <div class="avatar">
                        <?php if (!empty($usuario['foto_perfil'])): ?>
                            <img src="../uploads/usuarios/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil" id="avatar-img">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #e07b67, #cc624e); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 id="user-name-sidebar"><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                    <p>Membro desde <?php echo date('m/Y', strtotime($usuario['data_cadastro'] ?? 'now')); ?></p>
                </div>

                <ul class="menu-links">
                    <li><a href="perfil.php" class="ativo"><i class="fas fa-user-circle"></i> Meu Perfil</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </div>

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="conteudo-principal">
                <!-- SEÇÃO FOTO DE PERFIL -->
                <div class="secao-perfil">
                    <h2><i class="fas fa-camera"></i> Foto de Perfil</h2>
                    
                    <div class="upload-foto">
                        <div class="foto-preview" onclick="document.getElementById('foto_perfil').click()">
                            <?php if (!empty($usuario['foto_perfil'])): ?>
                                <img src="../uploads/usuarios/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil" id="foto-preview-img">
                            <?php else: ?>
                                <div id="default-avatar" style="width: 100%; height: 100%; background: linear-gradient(135deg, #e07b67, #cc624e); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
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

                    <form method="POST" action="" enctype="multipart/form-data" id="form-perfil">
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

                        <div class="form-group">
                            <label for="endereco">Endereço</label>
                            <textarea id="endereco" name="endereco" class="form-control" rows="3" placeholder="Digite seu endereço completo"><?php echo htmlspecialchars($usuario['endereco'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" name="atualizar_perfil" class="btn-primary">
                            <i class="fas fa-save"></i> Atualizar Perfil
                        </button>
                    </form>
                </div>

                <!-- SEÇÃO ALTERAR SENHA -->
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
            </div>
        </div>
    </main>

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
// 🔹 SISTEMA DE NOTIFICAÇÕES - IGUAL À PÁGINA SOBRE
function atualizarBadgeCarrinho() {
    const badge = document.getElementById('carrinhoBadge');
    const totalNotificacoes = <?php echo count($_SESSION['carrinho_notificacoes']); ?>;
    
    if (totalNotificacoes > 0) {
        badge.textContent = totalNotificacoes;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

function incrementarBadgeCarrinho() {
    const badge = document.getElementById('carrinhoBadge');
    let currentCount = parseInt(badge.textContent) || 0;
    currentCount++;
    
    badge.textContent = currentCount;
    badge.style.display = 'flex';
    
    // Animação de destaque
    badge.classList.add('badge-bounce');
    setTimeout(() => {
        badge.classList.remove('badge-bounce');
    }, 500);
}

// Atualizar badge quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    atualizarBadgeCarrinho();
});

// 🔹 PREVIEW DA IMAGEM EM TEMPO REAL
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Atualizar preview na seção de foto
            const previewImg = document.getElementById('foto-preview-img');
            const defaultAvatar = document.getElementById('default-avatar');
            
            if (previewImg) {
                previewImg.src = e.target.result;
            } else {
                // Se não existe a tag img, criar uma
                if (defaultAvatar) {
                    defaultAvatar.style.display = 'none';
                }
                
                const fotoPreview = document.querySelector('.foto-preview');
                const newImg = document.createElement('img');
                newImg.id = 'foto-preview-img';
                newImg.src = e.target.result;
                newImg.alt = 'Preview da foto';
                newImg.style.width = '100%';
                newImg.style.height = '100%';
                newImg.style.objectFit = 'cover';
                
                fotoPreview.insertBefore(newImg, fotoPreview.firstChild);
            }
            
            // Atualizar também o avatar no menu lateral
            const avatarImg = document.getElementById('avatar-img');
            if (avatarImg) {
                avatarImg.src = e.target.result;
            } else {
                const avatarDiv = document.querySelector('.avatar');
                const newAvatarImg = document.createElement('img');
                newAvatarImg.id = 'avatar-img';
                newAvatarImg.src = e.target.result;
                newAvatarImg.alt = 'Foto de perfil';
                newAvatarImg.style.width = '100%';
                newAvatarImg.style.height = '100%';
                newAvatarImg.style.objectFit = 'cover';
                newAvatarImg.style.borderRadius = '50%';
                
                // Remover conteúdo atual do avatar
                while (avatarDiv.firstChild) {
                    avatarDiv.removeChild(avatarDiv.firstChild);
                }
                avatarDiv.appendChild(newAvatarImg);
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// 🔹 VALIDAÇÃO DO TAMANHO DO ARQUIVO
document.getElementById('foto_perfil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // Tamanho em MB
        if (fileSize > 2) {
            alert('O arquivo é muito grande. Por favor, selecione uma imagem de até 2MB.');
            this.value = ''; // Limpa o input
            return false;
        }
        
        // Verificar tipo de arquivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Por favor, selecione apenas imagens nos formatos JPG, PNG ou GIF.');
            this.value = ''; // Limpa o input
            return false;
        }
    }
});
</script>

<script>
// Dropdown do perfil - IGUAL À PÁGINA SOBRE
document.addEventListener('DOMContentLoaded', function () {
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function (e) {
            if (!profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });

        // Prevenir fechamento ao clicar dentro do dropdown
        profileDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
});
</script>
</body>
</html>