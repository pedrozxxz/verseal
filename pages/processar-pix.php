<?php
session_start();

// DEBUG: Verificar toda a sessão
error_log("SESSION completa: " . print_r($_SESSION, true));

// Verificar se há dados do pedido na sessão
if (!isset($_SESSION['dados_pedido'])) {
    error_log("ERRO: dados_pedido não encontrado na sessão");
    header('Location: checkout.php');
    exit;
}

// Usar dados da sessão
$dados_pedido = $_SESSION['dados_pedido'];
$valor_total = $dados_pedido['pagamento']['valor_total'] ?? 0;
$nome_cliente = $dados_pedido['cliente']['nome'] ?? 'Cliente';

// DEBUG: Verificar dados específicos
error_log("Valor total no PIX: " . $valor_total);
error_log("Nome cliente no PIX: " . $nome_cliente);
error_log("Dados pedido completo: " . print_r($dados_pedido, true));

// VERIFICAÇÃO DA SESSÃO PARA NAVBAR
$usuarioLogado = null;
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
} elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
} elseif (isset($_SESSION["usuario"])) {
    $usuarioLogado = $_SESSION["usuario"];
    $tipoUsuario = "usuario";
}

// Gerar dados do pedido
$pedido_id = 'VS' . date('YmdHis') . rand(100, 999);
$codigo_pix = generatePixCode($valor_total, $pedido_id);

// Salvar pedido na sessão
$_SESSION['ultimo_pedido'] = [
    'id' => $pedido_id,
    'valor' => $valor_total,
    'cliente' => $nome_cliente,
    'metodo' => 'pix',
    'codigo_pix' => $codigo_pix,
    'status' => 'aguardando_pagamento',
    'data' => date('Y-m-d H:i:s')
];

// Limpar carrinho e dados temporários
unset($_SESSION['carrinho']);
unset($_SESSION['dados_pedido']);

function generatePixCode($valor, $pedido_id) {
    // Gerar um código PIX simulado (em um sistema real, usaria uma API de PIX)
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < 32; $i++) {
        $codigo .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $codigo;
}

// Gerar QR Code usando uma API online (exemplo com QR Server)
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($codigo_pix);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verseal - Pagamento PIX</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .pagina-pix {
            padding: 40px 7%;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        
        .status-pedido {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .qrcode-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 25px 0;
        }
        
        .qrcode {
            max-width: 250px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 10px;
        }
        
        .qrcode img {
            width: 100%;
            height: auto;
        }
        
        .codigo-pix {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
        }
        
        .btn-copiar {
            background: #cc624e;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px 5px;
        }
        
        .btn-copiar:hover {
            background: #e07b67;
        }
        
        .instrucoes {
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .instrucoes ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .instrucoes li {
            margin-bottom: 10px;
        }

        /* Estilos para o dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 220px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1000;
            padding: 10px 0;
        }
        
        .dropdown-content.show {
            display: block;
        }
        
        .user-info {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
        }
        
        .dropdown-item:hover {
            background: #f5f5f5;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 5px 0;
        }
        
        .logout-btn {
            color: #cc624e;
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
            <a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
            <div class="dropdown-content" id="profile-dropdown">
                <?php if ($usuarioLogado): ?>
                    <div class="user-info">
                        <p>Seja bem-vindo, 
                            <span id="user-name">
                                <?php 
                                if (is_array($usuarioLogado)) {
                                    echo htmlspecialchars($usuarioLogado['nome'] ?? $usuarioLogado['nome_artistico'] ?? 'Usuário');
                                } else {
                                    echo htmlspecialchars($usuarioLogado);
                                }
                                ?>
                            </span>!
                        </p>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
                    <div class="dropdown-divider"></div>
                    <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
                <?php else: ?>
                    <div class="user-info">
                        <p>Faça login para acessar seu perfil</p>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    <a href="./login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
                    <a href="./login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<main class="pagina-pix">
    <h1><i class="fas fa-qrcode"></i> Pagamento PIX</h1>
    
    <div class="status-pedido">
        <h3>Pedido: <?php echo htmlspecialchars($pedido_id); ?></h3>
        <p>Status: <strong>Aguardando pagamento</strong></p>
        <p>Cliente: <strong><?php echo htmlspecialchars($nome_cliente); ?></strong></p>
        <p>Valor: <strong>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></strong></p>
    </div>

    <div class="qrcode-container">
        <h3>Escaneie o QR Code</h3>
        <div class="qrcode">
            <!-- QR Code real gerado via API -->
            <img src="<?php echo $qr_code_url; ?>" alt="QR Code PIX">
        </div>
        
        <p>Ou use o código PIX:</p>
        <div class="codigo-pix" id="codigo-pix">
            <?php echo htmlspecialchars($codigo_pix); ?>
        </div>
        
        <button class="btn-copiar" onclick="copiarCodigoPix()">
            <i class="fas fa-copy"></i> Copiar Código PIX
        </button>
    </div>

    <div class="instrucoes">
        <h3>Como pagar:</h3>
        <ol>
            <li>Abra o app do seu banco</li>
            <li>Selecione "Pagar com PIX"</li>
            <li>Escaneie o QR Code ou cole o código</li>
            <li>Confirme o pagamento</li>
            <li>O status será atualizado automaticamente</li>
        </ol>
        
        <p><strong>Prazo:</strong> O pagamento deve ser realizado em até 30 minutos.</p>
    </div>

    <div style="margin-top: 30px;">
        <button class="btn-copiar" onclick="window.location.href='../index.php'">
            <i class="fas fa-home"></i> Voltar ao Início
        </button>
        <button class="btn-copiar" onclick="verificarPagamento()">
            <i class="fas fa-sync"></i> Verificar Pagamento
        </button>
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
function copiarCodigoPix() {
    const codigo = document.getElementById('codigo-pix').textContent;
    navigator.clipboard.writeText(codigo).then(() => {
        alert('Código PIX copiado!');
    }).catch(() => {
        // Fallback para navegadores mais antigos
        const textArea = document.createElement('textarea');
        textArea.value = codigo;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Código PIX copiado!');
    });
}

function verificarPagamento() {
    if (confirm('Em um sistema real, esta função verificaria se o pagamento foi confirmado. Deseja simular pagamento confirmado?')) {
        alert('Pagamento confirmado! Obrigado pela compra.');
        window.location.href = 'sucesso-compra.php';
    }
}

// Verificar automaticamente após 30 segundos
setTimeout(() => {
    if (confirm('Pagamento ainda não identificado. Deseja continuar aguardando?')) {
        location.reload();
    }
}, 30000);

// Dropdown do perfil
const profileIcon = document.getElementById("profile-icon");
const profileDropdown = document.getElementById("profile-dropdown");

if (profileIcon && profileDropdown) {
    profileIcon.addEventListener("click", (e) => {
        e.preventDefault();
        profileDropdown.classList.toggle("show");
    });

    // Fechar dropdown ao clicar fora
    document.addEventListener("click", (e) => {
        if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
            profileDropdown.classList.remove("show");
        }
    });
}
</script>

</body>
</html>