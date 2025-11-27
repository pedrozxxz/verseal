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

// Conexão com o banco de dados
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

// DEBUG: Verificar dados específicos
error_log("Valor total no PIX: " . $valor_total);
error_log("Nome cliente no PIX: " . $nome_cliente);
error_log("Dados pedido completo: " . print_r($dados_pedido, true));

// VERIFICAÇÃO DA SESSÃO PARA NAVBAR E USUÁRIO
$usuarioLogado = null;
$usuario_id = null;

if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
    $usuario_id = is_array($usuarioLogado) ? $usuarioLogado['id'] : null;
} elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
    $usuario_id = is_array($usuarioLogado) ? $usuarioLogado['id'] : null;
} elseif (isset($_SESSION["usuario"])) {
    $usuarioLogado = $_SESSION["usuario"];
    $tipoUsuario = "usuario";
    $usuario_id = is_array($usuarioLogado) ? $usuarioLogado['id'] : null;
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
    'data' => date('Y-m-d H:i:s'),
    'usuario_id' => $usuario_id
];

function generatePixCode($valor, $pedido_id) {
    // Gerar um código PIX simulado (em um sistema real, usaria uma API de PIX)
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < 32; $i++) {
        $codigo .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $codigo;
}

// Função para registrar pedido no banco de dados
function registrarPedido($conn, $pedido_data) {
    try {
        // Iniciar transação
        $conn->begin_transaction();
        
        // 1. Inserir na tabela pedidos
        $sql_pedido = "INSERT INTO pedidos (usuario_id, codigo_pedido, valor_total, status, metodo_pagamento, data_pedido) 
                      VALUES (?, ?, ?, 'aguardando_pagamento', 'pix', NOW())";
        
        $stmt_pedido = $conn->prepare($sql_pedido);
        
        // Garantir que os valores não sejam nulos
        $usuario_id = $pedido_data['usuario_id'] ?? null;
        $codigo_pedido = $pedido_data['codigo_pedido'] ?? '';
        $valor_total = $pedido_data['valor_total'] ?? 0;
        
        $stmt_pedido->bind_param("isd", 
            $usuario_id,
            $codigo_pedido,
            $valor_total
        );
        
        if (!$stmt_pedido->execute()) {
            throw new Exception("Erro ao inserir pedido: " . $stmt_pedido->error);
        }
        
        $pedido_id = $conn->insert_id;
        $stmt_pedido->close();
        
        // 2. Inserir na tabela enderecos_entrega (se existirem dados de endereço)
        if (isset($pedido_data['endereco']) && !empty($pedido_data['endereco'])) {
            $sql_endereco = "INSERT INTO enderecos_entrega (pedido_id, nome_destinatario, cep, logradouro, numero, complemento, bairro, cidade, estado, telefone) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_endereco = $conn->prepare($sql_endereco);
            
            // Garantir que todos os valores tenham valores padrão
            $endereco = $pedido_data['endereco'];
            $nome_destinatario = $endereco['nome_destinatario'] ?? '';
            $cep = $endereco['cep'] ?? '';
            $logradouro = $endereco['logradouro'] ?? '';
            $numero = $endereco['numero'] ?? '';
            $complemento = $endereco['complemento'] ?? '';
            $bairro = $endereco['bairro'] ?? '';
            $cidade = $endereco['cidade'] ?? '';
            $estado = $endereco['estado'] ?? '';
            $telefone = $endereco['telefone'] ?? '';
            
            $stmt_endereco->bind_param("isssssssss",
                $pedido_id,
                $nome_destinatario,
                $cep,
                $logradouro,
                $numero,
                $complemento,
                $bairro,
                $cidade,
                $estado,
                $telefone
            );
            
            if (!$stmt_endereco->execute()) {
                throw new Exception("Erro ao inserir endereço: " . $stmt_endereco->error);
            }
            $stmt_endereco->close();
        }
        
        // 3. Inserir/Atualizar na tabela vendas (por mês/ano)
        $mes = date('m');
        $ano = date('Y');
        
        // Verificar se já existe registro para este mês/ano
        $sql_check_venda = "SELECT id, valor_total FROM vendas WHERE mes = ? AND ano = ?";
        $stmt_check = $conn->prepare($sql_check_venda);
        $stmt_check->bind_param("ii", $mes, $ano);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            // Atualizar registro existente
            $venda_existente = $result_check->fetch_assoc();
            $novo_valor = $venda_existente['valor_total'] + $valor_total;
            
            $sql_update_venda = "UPDATE vendas SET valor_total = ?, data_registro = NOW() WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update_venda);
            $stmt_update->bind_param("di", $novo_valor, $venda_existente['id']);
            
            if (!$stmt_update->execute()) {
                throw new Exception("Erro ao atualizar venda: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            // Inserir novo registro
            $sql_insert_venda = "INSERT INTO vendas (mes, ano, valor_total, data_registro) VALUES (?, ?, ?, NOW())";
            $stmt_insert = $conn->prepare($sql_insert_venda);
            $stmt_insert->bind_param("iid", $mes, $ano, $valor_total);
            
            if (!$stmt_insert->execute()) {
                throw new Exception("Erro ao inserir venda: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
        
        // Confirmar transação
        $conn->commit();
        
        return [
            'success' => true,
            'pedido_id' => $pedido_id,
            'codigo_pedido' => $codigo_pedido
        ];
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        error_log("Erro ao registrar pedido: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Função para confirmar pagamento
function confirmarPagamento($conn, $codigo_pedido) {
    try {
        $conn->begin_transaction();
        
        // 1. Atualizar status do pedido
        $sql_update_pedido = "UPDATE pedidos SET status = 'pago', data_pagamento = NOW() WHERE codigo_pedido = ?";
        $stmt_pedido = $conn->prepare($sql_update_pedido);
        $stmt_pedido->bind_param("s", $codigo_pedido);
        
        if (!$stmt_pedido->execute()) {
            throw new Exception("Erro ao atualizar pedido: " . $stmt_pedido->error);
        }
        
        if ($stmt_pedido->affected_rows === 0) {
            throw new Exception("Pedido não encontrado: " . $codigo_pedido);
        }
        
        $stmt_pedido->close();
        
        // 2. Buscar dados do pedido para log
        $sql_pedido = "SELECT valor_total FROM pedidos WHERE codigo_pedido = ?";
        $stmt_info = $conn->prepare($sql_pedido);
        $stmt_info->bind_param("s", $codigo_pedido);
        $stmt_info->execute();
        $pedido_info = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();
        
        $conn->commit();
        
        return [
            'success' => true,
            'valor_total' => $pedido_info['valor_total'],
            'codigo_pedido' => $codigo_pedido
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro ao confirmar pagamento: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Preparar dados para registro
$dados_registro = [
    'usuario_id' => $usuario_id,
    'codigo_pedido' => $pedido_id,
    'valor_total' => $valor_total
];

// Adicionar dados de endereço apenas se existirem
if (isset($dados_pedido['endereco']) && is_array($dados_pedido['endereco'])) {
    $dados_registro['endereco'] = [
        'nome_destinatario' => $dados_pedido['cliente']['nome'] ?? '',
        'cep' => $dados_pedido['endereco']['cep'] ?? '',
        'logradouro' => $dados_pedido['endereco']['endereco'] ?? '',
        'numero' => $dados_pedido['endereco']['numero'] ?? '',
        'complemento' => $dados_pedido['endereco']['complemento'] ?? '',
        'bairro' => $dados_pedido['endereco']['bairro'] ?? '',
        'cidade' => $dados_pedido['endereco']['cidade'] ?? '',
        'estado' => $dados_pedido['endereco']['estado'] ?? '',
        'telefone' => $dados_pedido['cliente']['telefone'] ?? ''
    ];
}

// Registrar pedido inicial
if (!isset($_SESSION['pedido_registrado'])) {
    $resultado_registro = registrarPedido($conn, $dados_registro);
    
    if ($resultado_registro['success']) {
        $_SESSION['pedido_registrado'] = true;
        $_SESSION['codigo_pedido_db'] = $pedido_id;
        error_log("Pedido registrado com sucesso: " . $pedido_id);
    } else {
        error_log("Erro ao registrar pedido: " . $resultado_registro['error']);
        // Não impedir o fluxo mesmo com erro no banco
    }
}

// Processar confirmação de pagamento via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirmar_pagamento') {
    $codigo_pedido = $_SESSION['codigo_pedido_db'] ?? $pedido_id;
    $resultado = confirmarPagamento($conn, $codigo_pedido);
    
    header('Content-Type: application/json');
    echo json_encode($resultado);
    exit;
}

// Gerar QR Code usando uma API online
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($codigo_pix);

$conn->close();
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
        <button class="btn-copiar" id="btn-verificar" onclick="verificarPagamento()">
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