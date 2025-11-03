<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$usuarioLogado = $_SESSION['usuario'];
$usuarioId = is_array($usuarioLogado) ? $usuarioLogado['id'] : null;
$usuarioNome = is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado;

// Buscar notificações do usuário
$sqlNotificacoes = "SELECT * FROM notificacoes WHERE usuario_id = ? OR usuario_id IS NULL ORDER BY data_criacao DESC";
$stmtNotificacoes = $conn->prepare($sqlNotificacoes);
$stmtNotificacoes->bind_param("i", $usuarioId);
$stmtNotificacoes->execute();
$resultNotificacoes = $stmtNotificacoes->get_result();

$notificacoes = [];
while ($notificacao = $resultNotificacoes->fetch_assoc()) {
    $notificacoes[] = $notificacao;
}

// Buscar mensagens recebidas (para artistas)
$sqlMensagens = "SELECT m.*, a.nome as remetente_nome 
                 FROM mensagens m 
                 LEFT JOIN artistas a ON m.remetente_id = a.id 
                 WHERE m.destinatario_id = ? 
                 ORDER BY m.data_envio DESC";
$stmtMensagens = $conn->prepare($sqlMensagens);
$stmtMensagens->bind_param("i", $usuarioId);
$stmtMensagens->execute();
$resultMensagens = $stmtMensagens->get_result();

$mensagens = [];
while ($mensagem = $resultMensagens->fetch_assoc()) {
    $mensagens[] = $mensagem;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - Verseal</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        .page-title {
            color: #cc624e;
            font-family: 'Playfair Display', serif;
            margin-bottom: 30px;
            text-align: center;
        }

        .notificacao-item, .mensagem-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #cc624e;
        }

        .notificacao-item.nao-lida, .mensagem-item.nao-lida {
            border-left-color: #28a745;
            background: #f8fff9;
        }

        .notificacao-header, .mensagem-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .notificacao-titulo, .mensagem-assunto {
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .notificacao-data, .mensagem-data {
            color: #666;
            font-size: 0.9rem;
        }

        .notificacao-mensagem, .mensagem-conteudo {
            color: #555;
            line-height: 1.5;
        }

        .section-title {
            color: #333;
            font-family: 'Playfair Display', serif;
            margin: 30px 0 15px 0;
            border-bottom: 2px solid #cc624e;
            padding-bottom: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
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
            
            <a href="./carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
            
            <div class="profile-dropdown">
                <a href="#" class="icon-link" id="profile-icon">
                    <i class="fas fa-user"></i>
                </a>
                <div class="dropdown-content" id="profile-dropdown">
                    <div class="user-info">
                        <p>Seja bem-vindo, <span><?php echo htmlspecialchars($usuarioNome); ?></span>!</p>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
                    <a href="./notificacoes.php" class="dropdown-item"><i class="fas fa-bell"></i> Notificações</a>
                    <div class="dropdown-divider"></div>
                    <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1 class="page-title">Minhas Notificações</h1>

            <!-- Mensagens Recebidas -->
            <h2 class="section-title">Mensagens Recebidas</h2>
            <?php if (empty($mensagens)): ?>
                <div class="empty-state">
                    <i class="fas fa-envelope-open"></i>
                    <h3>Nenhuma mensagem</h3>
                    <p>Você não recebeu nenhuma mensagem ainda.</p>
                </div>
            <?php else: ?>
                <?php foreach ($mensagens as $mensagem): ?>
                    <div class="mensagem-item <?php echo $mensagem['lida'] ? '' : 'nao-lida'; ?>">
                        <div class="mensagem-header">
                            <h3 class="mensagem-assunto"><?php echo htmlspecialchars($mensagem['assunto']); ?></h3>
                            <span class="mensagem-data"><?php echo date('d/m/Y H:i', strtotime($mensagem['data_envio'])); ?></span>
                        </div>
                        <p class="mensagem-conteudo">
                            <strong>De:</strong> <?php echo htmlspecialchars($mensagem['remetente_nome'] ?? 'Usuário'); ?><br>
                            <?php echo htmlspecialchars($mensagem['mensagem']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Notificações do Sistema -->
            <h2 class="section-title">Notificações do Sistema</h2>
            <?php if (empty($notificacoes)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>Nenhuma notificação</h3>
                    <p>Você não tem notificações no momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notificacoes as $notificacao): ?>
                    <div class="notificacao-item <?php echo $notificacao['lida'] ? '' : 'nao-lida'; ?>">
                        <div class="notificacao-header">
                            <h3 class="notificacao-titulo"><?php echo htmlspecialchars($notificacao['titulo']); ?></h3>
                            <span class="notificacao-data"><?php echo date('d/m/Y H:i', strtotime($notificacao['data_criacao'])); ?></span>
                        </div>
                        <p class="notificacao-mensagem"><?php echo htmlspecialchars($notificacao['mensagem']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Dropdown do perfil
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