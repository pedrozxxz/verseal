<?php
session_start();

// Inicializar variáveis com valores padrão
$pedido_id = '';
$valor_total = 0;
$nome_cliente = '';
$codigo_barras = '';
$vencimento = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simular dados do pedido
    $pedido_id = 'VS' . date('YmdHis') . rand(100, 999);
    $valor_total = $_POST['valor_total'] ?? 0;
    $nome_cliente = $_POST['nome_cliente'] ?? '';
    
    // Gerar dados do boleto (simulado)
    $codigo_barras = generateCodigoBarras();
    $vencimento = date('d/m/Y', strtotime('+3 days'));
    
    // Salvar pedido na sessão
    $_SESSION['ultimo_pedido'] = [
        'id' => $pedido_id,
        'valor' => $valor_total,
        'cliente' => $nome_cliente,
        'metodo' => 'boleto',
        'codigo_barras' => $codigo_barras,
        'vencimento' => $vencimento,
        'status' => 'aguardando_pagamento',
        'data' => date('Y-m-d H:i:s')
    ];
    
    // Limpar carrinho
    unset($_SESSION['carrinho']);
} else {
    // Se não for POST, tentar recuperar dados da sessão
    if (isset($_SESSION['ultimo_pedido'])) {
        $pedido_id = $_SESSION['ultimo_pedido']['id'] ?? '';
        $valor_total = $_SESSION['ultimo_pedido']['valor'] ?? 0;
        $nome_cliente = $_SESSION['ultimo_pedido']['cliente'] ?? '';
        $codigo_barras = $_SESSION['ultimo_pedido']['codigo_barras'] ?? '';
        $vencimento = $_SESSION['ultimo_pedido']['vencimento'] ?? '';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verseal - Boleto Bancário</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .pagina-boleto {
            padding: 40px 7%;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .status-pedido {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .boleto-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 25px 0;
        }
        
        .linha-boleto {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .codigo-barras {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-family: monospace;
            font-size: 1.2rem;
            letter-spacing: 2px;
            color: #000;
            border: 1px solid #ddd;
        }
        
        .btn-imprimir {
            background: #cc624e;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        
        .btn-imprimir:hover {
            background: #e07b67;
        }

        .btn-pagar {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        
        .btn-pagar:hover {
            background: #34ce57;
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

        .botoes-acao {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Verseal</div>
    <nav>
        <a href="../index.php">Início</a>
        <a href="./produto.php">Obras</a>
    </nav>
</header>

<main class="pagina-boleto">
    <h1><i class="fas fa-barcode"></i> Boleto Bancário</h1>
    
    <div class="status-pedido">
        <h3>Pedido: <?php echo $pedido_id; ?></h3>
        <p>Status: <strong>Aguardando pagamento</strong></p>
        <p>Valor: <strong>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></strong></p>
    </div>

    <?php if (!empty($pedido_id)): ?>
    <div class="boleto-container">
        <h3>Boleto Bancário</h3>
        
        <div class="linha-boleto">
            <span>Beneficiário:</span>
            <strong>Verseal Galeria de Arte LTDA</strong>
        </div>
        
        <div class="linha-boleto">
            <span>Pagador:</span>
            <strong><?php echo htmlspecialchars($nome_cliente); ?></strong>
        </div>
        
        <div class="linha-boleto">
            <span>Vencimento:</span>
            <strong><?php echo $vencimento; ?></strong>
        </div>
        
        <div class="linha-boleto">
            <span>Valor:</span>
            <strong>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></strong>
        </div>
        
        <div class="codigo-barras" id="codigo-barras">
            <?php echo $codigo_barras; ?>
        </div>
        
        <p><strong>Linha digitável:</strong></p>
        <div class="codigo-barras" id="linha-digitavel">
            <?php echo formatLinhaDigitavel($codigo_barras); ?>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn-imprimir" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir Boleto
            </button>
            <button class="btn-imprimir" onclick="copiarCodigoBarras()">
                <i class="fas fa-copy"></i> Copiar Código
            </button>
        </div>
    </div>

    <div class="instrucoes">
        <h3>Instruções de pagamento:</h3>
        <ol>
            <li>Imprima o boleto ou anote o código de barras</li>
            <li>Pague em qualquer banco, lotérica ou internet banking</li>
            <li>O prazo de vencimento é de 3 dias úteis</li>
            <li>Após o pagamento, a confirmação pode levar 1-2 dias úteis</li>
            <li>Você receberá um email quando o pagamento for confirmado</li>
        </ol>
        
        <p><strong>Importante:</strong> O pedido será processado somente após a confirmação do pagamento.</p>
    </div>

    <div class="botoes-acao">
        <button class="btn-pagar" onclick="simularPagamento()">
            <i class="fas fa-check-circle"></i> Simular Pagamento
        </button>
        <button class="btn-imprimir" onclick="window.location.href='../index.php'">
            <i class="fas fa-home"></i> Voltar ao Início
        </button>
    </div>
    <?php else: ?>
    <div class="boleto-container" style="text-align: center;">
        <h3>Nenhum pedido encontrado</h3>
        <p>Não foi possível gerar o boleto. Por favor, realize o pedido novamente.</p>
        <button class="btn-imprimir" onclick="window.location.href='../index.php'">
            <i class="fas fa-home"></i> Voltar ao Início
        </button>
    </div>
    <?php endif; ?>
</main>

<script>
function copiarCodigoBarras() {
    const codigo = document.getElementById('codigo-barras').textContent;
    navigator.clipboard.writeText(codigo).then(() => {
        alert('Código de barras copiado!');
    });
}

function simularPagamento() {
    if (confirm('Deseja simular o pagamento deste boleto? O status do pedido será atualizado para "Pago".')) {
        // Simular requisição AJAX para atualizar status
        fetch('simular-pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                pedido_id: '<?php echo $pedido_id; ?>',
                acao: 'simular_pagamento'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pagamento simulado com sucesso! Status do pedido atualizado.');
                // Atualizar a página para refletir mudanças
                window.location.reload();
            } else {
                alert('Erro ao simular pagamento: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao simular pagamento. Tente novamente.');
        });
    }
}

// Configuração de impressão
window.onbeforeprint = function() {
    document.querySelector('header').style.display = 'none';
    document.querySelector('footer').style.display = 'none';
};

window.onafterprint = function() {
    document.querySelector('header').style.display = 'block';
    document.querySelector('footer').style.display = 'block';
};
</script>

</body>
</html>

<?php
function generateCodigoBarras() {
    // Gerar código de barras simulado (44 dígitos)
    $codigo = '';
    for ($i = 0; $i < 44; $i++) {
        $codigo .= rand(0, 9);
    }
    return $codigo;
}

function formatLinhaDigitavel($codigo_barras) {
    // Formatar linha digitável no padrão brasileiro
    if (empty($codigo_barras)) {
        return '';
    }
    return substr($codigo_barras, 0, 5) . '.' . 
           substr($codigo_barras, 5, 5) . ' ' .
           substr($codigo_barras, 10, 5) . '.' . 
           substr($codigo_barras, 15, 6) . ' ' .
           substr($codigo_barras, 21, 5) . '.' . 
           substr($codigo_barras, 26, 6) . ' ' .
           substr($codigo_barras, 32, 1) . ' ' .
           substr($codigo_barras, 33);
}
?>