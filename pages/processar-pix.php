<?php
session_start();

// Habilitar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se há dados do pedido na sessão
if (!isset($_SESSION['dados_pedido'])) {
    header('Location: checkout.php');
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

// Usar dados da sessão
$dados_pedido = $_SESSION['dados_pedido'];
$valor_total = $dados_pedido['pagamento']['valor_total'] ?? 0;
$nome_cliente = $dados_pedido['cliente']['nome'] ?? 'Cliente';

// VERIFICAÇÃO DA SESSÃO PARA NAVBAR
$usuarioLogado = null;
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
} elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}

// Buscar pedido da sessão
$pedido_id = $_SESSION['ultimo_pedido']['id'] ?? 'VS' . date('YmdHis') . rand(100, 999);
$pedido_db_id = $_SESSION['ultimo_pedido']['id_db'] ?? null;

// Gerar código PIX
$codigo_pix = generatePixCode($valor_total, $pedido_id);

// Verificar se a coluna codigo_pix existe antes de tentar atualizar
$check_column_sql = "SHOW COLUMNS FROM pedidos LIKE 'codigo_pix'";
$result = $conn->query($check_column_sql);

if ($result->num_rows > 0) {
    // Coluna existe, podemos atualizar
    if ($pedido_db_id) {
        $sql_update = "UPDATE pedidos SET codigo_pix = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $codigo_pix, $pedido_db_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
} else {
    // Coluna não existe, vamos criá-la
    $alter_sql = "ALTER TABLE pedidos ADD COLUMN codigo_pix VARCHAR(255) NULL AFTER metodo_pagamento";
    if ($conn->query($alter_sql) === TRUE) {
        // Agora atualiza o pedido
        if ($pedido_db_id) {
            $sql_update = "UPDATE pedidos SET codigo_pix = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $codigo_pix, $pedido_db_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    } else {
        error_log("Erro ao criar coluna codigo_pix: " . $conn->error);
    }
}

// Função para confirmar pagamento PIX
function confirmarPagamentoPIX($conn, $pedido_db_id) {
    try {
        $conn->begin_transaction();
        
        // Atualizar status do pedido para pago
        $sql_update = "UPDATE pedidos SET status = 'pago', data_pagamento = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("i", $pedido_db_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar pedido: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Atualizar tabela vendas
        $sql_pedido = "SELECT valor_total FROM pedidos WHERE id = ?";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("i", $pedido_db_id);
        $stmt_pedido->execute();
        $pedido_info = $stmt_pedido->get_result()->fetch_assoc();
        $stmt_pedido->close();
        
        $valor_total = $pedido_info['valor_total'] ?? 0;
        $mes = date('m');
        $ano = date('Y');
        
        $sql_check_venda = "SELECT id, valor_total FROM vendas WHERE mes = ? AND ano = ?";
        $stmt_check = $conn->prepare($sql_check_venda);
        $stmt_check->bind_param("ii", $mes, $ano);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $venda_existente = $result_check->fetch_assoc();
            $novo_valor = $venda_existente['valor_total'] + $valor_total;
            
            $sql_update_venda = "UPDATE vendas SET valor_total = ?, data_registro = NOW() WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update_venda);
            $stmt_update->bind_param("di", $novo_valor, $venda_existente['id']);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $sql_insert_venda = "INSERT INTO vendas (mes, ano, valor_total, data_registro) VALUES (?, ?, ?, NOW())";
            $stmt_insert = $conn->prepare($sql_insert_venda);
            $stmt_insert->bind_param("iid", $mes, $ano, $valor_total);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_check->close();
        
        $conn->commit();
        
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro ao confirmar pagamento PIX: " . $e->getMessage());
        return false;
    }
}

// Processar confirmação de pagamento via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'confirmar_pagamento') {
        if ($pedido_db_id) {
            $sucesso = confirmarPagamentoPIX($conn, $pedido_db_id);
            
            if ($sucesso) {
                // Atualizar sessão
                $_SESSION['ultimo_pedido']['status'] = 'pago';
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pagamento confirmado com sucesso!',
                    'codigo_pedido' => $pedido_id
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao confirmar pagamento'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Pedido não encontrado'
            ]);
        }
        exit;
    }
}

// Gerar QR Code
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($codigo_pix);

$conn->close();

function generatePixCode($valor, $pedido_id) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < 32; $i++) {
        $codigo .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $codigo;
}
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

        .status-pago {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
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
    
    <div class="status-pedido" id="status-pedido">
        <h3>Pedido: <?php echo htmlspecialchars($pedido_id); ?></h3>
        <p>Status: <strong id="status-text">Aguardando pagamento</strong></p>
        <p>Cliente: <strong><?php echo htmlspecialchars($nome_cliente); ?></strong></p>
        <p>Valor: <strong>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></strong></p>
    </div>

    <div class="qrcode-container" id="qrcode-container">
        <h3>Escaneie o QR Code</h3>
        <div class="qrcode">
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
            <li>Clique em "Verificar Pagamento" abaixo</li>
        </ol>
        
        <p><strong>Prazo:</strong> O pagamento deve ser realizado em até 30 minutos.</p>
    </div>

    <div style="margin-top: 30px;">
        <button class="btn-copiar" onclick="window.location.href='../index.php'">
            <i class="fas fa-home"></i> Voltar ao Início
        </button>
        <button class="btn-copiar" id="btn-verificar" onclick="verificarPagamento()">
            <i class="fas fa-sync"></i> Verificar Pagamento
        </button>
    </div>
</main>

<script>
function copiarCodigoPix() {
    const codigo = document.getElementById('codigo-pix').textContent;
    navigator.clipboard.writeText(codigo).then(() => {
        alert('Código PIX copiado!');
    });
}

function verificarPagamento() {
    const btnVerificar = document.getElementById('btn-verificar');
    btnVerificar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    btnVerificar.disabled = true;

    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=confirmar_pagamento'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar interface
            document.getElementById('status-text').textContent = 'Pago';
            document.getElementById('status-pedido').classList.add('status-pago');
            document.getElementById('qrcode-container').style.display = 'none';
            
            // Redirecionar para página de sucesso
            setTimeout(() => {
                window.location.href = 'sucesso-compra.php?pedido=' + data.codigo_pedido;
            }, 2000);
        } else {
            alert('Pagamento ainda não identificado. Tente novamente em alguns instantes.');
            btnVerificar.innerHTML = '<i class="fas fa-sync"></i> Verificar Pagamento';
            btnVerificar.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao verificar pagamento. Tente novamente.');
        btnVerificar.innerHTML = '<i class="fas fa-sync"></i> Verificar Pagamento';
        btnVerificar.disabled = false;
    });
}

// Verificar automaticamente após 30 segundos
setTimeout(() => {
    if (document.getElementById('status-text').textContent === 'Aguardando pagamento') {
        if (confirm('Pagamento ainda não identificado. Deseja verificar agora?')) {
            verificarPagamento();
        }
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

    document.addEventListener("click", (e) => {
        if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
            profileDropdown.classList.remove("show");
        }
    });
}
</script>

</body>
</html>