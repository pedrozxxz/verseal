<?php
session_start();
require_once '../config/database.php';

// Verificar se é admin
if (!isset($_SESSION["tipo_usuario"]) || $_SESSION["tipo_usuario"] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Processar o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_cliente'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $senha = $_POST['senha'];
    
    // Validações básicas
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = "O nome é obrigatório.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido.";
    }
    
    if (empty($senha) || strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    }
    
    // Verificar se email já existe
    if (empty($erros)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $erros[] = "Este email já está cadastrado.";
        }
    }
    
    // Se não há erros, cadastrar o cliente
    if (empty($erros)) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tipo, ativo) VALUES (?, ?, ?, ?, 'usuario', 1)");
            $stmt->execute([$nome, $email, $telefone, $senhaHash]);
            
            $cliente_id = $pdo->lastInsertId();
            
            // Redirecionar com mensagem de sucesso
            header("Location: adm-cliente.php?sucesso=1&nome=" . urlencode($nome));
            exit;
            
        } catch (PDOException $e) {
            $erros[] = "Erro ao cadastrar cliente: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente - Verseal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8f4f2, #fff9f8);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            width: 90%;
            max-width: 500px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container h1 {
            text-align: center;
            font-size: 2rem;
            color: #db6d56;
            margin-bottom: 25px;
            text-shadow: 1px 1px 4px rgba(219, 109, 86, 0.3);
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px 45px 12px 12px;
            border-radius: 12px;
            border: 1px solid #ccc;
            outline: none;
            transition: 0.3s;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: #db6d56;
            box-shadow: 0 2px 8px rgba(219, 109, 86, 0.2);
        }

        .form-group .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #db6d56;
            font-size: 1.1rem;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .save-btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #db6d56, #a7503e);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(219, 109, 86, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .save-btn:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #a7503e, #db6d56);
        }

        .cancel-btn {
            flex: 1;
            padding: 14px;
            border: 2px solid #db6d56;
            border-radius: 12px;
            background: transparent;
            color: #db6d56;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .cancel-btn:hover {
            background: #db6d56;
            color: white;
            transform: scale(1.03);
        }

        .error-message {
            background: #ffeaea;
            color: #d63031;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #d63031;
        }

        .error-message ul {
            margin-left: 20px;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            z-index: 2;
        }

        .password-toggle:hover {
            color: #db6d56;
        }

        @media(max-width:500px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .password-toggle {
                right: 35px;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Novo Cliente</h1>
        
        <?php if (!empty($erros)): ?>
            <div class="error-message">
                <strong>Erros encontrados:</strong>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nome">Nome completo *</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome do cliente" 
                       value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="Digite o email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" 
                       value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>">
                <i class="fas fa-phone input-icon"></i>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha *</label>
                <div class="password-container">
                    <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required minlength="6">
                    <span class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>
            
            <div class="btn-group">
                <a href="adm-cliente.php" class="cancel-btn">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" name="cadastrar_cliente" class="save-btn">
                    <i class="fas fa-save"></i> Cadastrar
                </button>
            </div>
        </form>
    </div>

    <script>
        // Mostrar/ocultar senha
        function togglePassword() {
            const senhaInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                senhaInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }

        // Formatação do telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 11) {
                if (value.length <= 2) {
                    value = value.replace(/^(\d{0,2})/, '($1');
                } else if (value.length <= 6) {
                    value = value.replace(/^(\d{2})(\d{0,4})/, '($1) $2');
                } else if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else {
                    value = value.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                }
            }
            
            e.target.value = value;
        });

        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value;
            
            if (!nome) {
                e.preventDefault();
                Swal.fire('Erro!', 'Por favor, preencha o nome do cliente.', 'error');
                return;
            }
            
            if (!email || !isValidEmail(email)) {
                e.preventDefault();
                Swal.fire('Erro!', 'Por favor, insira um email válido.', 'error');
                return;
            }
            
            if (senha.length < 6) {
                e.preventDefault();
                Swal.fire('Erro!', 'A senha deve ter pelo menos 6 caracteres.', 'error');
                return;
            }
        });

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // SweetAlert para sucesso (se veio do redirecionamento)
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Cliente cadastrado!',
                html: 'Cliente <strong><?php echo isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : ''; ?></strong> cadastrado com sucesso!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'adm-cliente.php';
            });
        <?php endif; ?>
    </script>
</body>

</html>