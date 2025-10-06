<?php
session_start();

// Verificar se há dados do pedido na sessão
if (!isset($_SESSION['dados_pedido'])) {
    header('Location: checkout.php');
    exit;
}

// Usar dados da sessão
$dados_pedido = $_SESSION['dados_pedido'];
$valor_total = $dados_pedido['pagamento']['valor_total'];
$nome_cliente = $dados_pedido['cliente']['nome'];

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
        
        header {
            background: white;
            padding: 1rem 7%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: bold;
            color: #cc624e;
        }
        
        nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }
        
        nav a:hover {
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
        <a href="./carrinho.php">Carrinho</a>
    </nav>
</header>

<main class="pagina-pix">
    <h1><i class="fas fa-qrcode"></i> Pagamento PIX</h1>
    
    <div class="status-pedido">
        <h3>Pedido: <?php echo htmlspecialchars($pedido_id); ?></h3>
        <p>Status: <strong>Aguardando pagamento</strong></p>
        <p>Valor: <strong>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></strong></p>
    </div>

    <div class="qrcode-container">
        <h3>Escaneie o QR Code</h3>
        <div class="qrcode">
            <!-- QR Code simulado -->
            <div style="width: 200px; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fas fa-qrcode" style="font-size: 4rem; color: #666;"></i>
            </div>
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
</script>

</body>
</html>