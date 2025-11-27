<?php
session_start();

// Inicializar variáveis com valores padrão
$pedido_id = '';
$valor_total = 0;
$nome_cliente = '';
$codigo_barras = '';
$vencimento = '';

// Função para calcular dias úteis
function calcularVencimento($dias_uteis = 3) {
    $data = new DateTime();
    $dias_adicionados = 0;
    
    while ($dias_adicionados < $dias_uteis) {
        $data->modify('+1 day');
        // Verifica se não é final de semana (6 = sábado, 7 = domingo)
        if ($data->format('N') < 6) {
            $dias_adicionados++;
        }
    }
    
    return $data->format('d/m/Y');
}

// Verificar se há dados do pedido na sessão
if (isset($_SESSION['dados_pedido'])) {
    $dados_pedido = $_SESSION['dados_pedido'];
    $valor_total = $dados_pedido['pagamento']['valor_total'] ?? 0;
    $nome_cliente = $dados_pedido['cliente']['nome'] ?? '';
    
    // Gerar dados do pedido
    $pedido_id = 'VS' . date('YmdHis') . rand(100, 999);
    
    // Gerar dados do boleto (simulado)
    $codigo_barras = generateCodigoBarras();
    $vencimento = calcularVencimento(3); // 3 dias úteis
    
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
    // Se não tiver dados na sessão, tentar recuperar da sessão anterior
    if (isset($_SESSION['ultimo_pedido'])) {
        $pedido_id = $_SESSION['ultimo_pedido']['id'] ?? '';
        $valor_total = $_SESSION['ultimo_pedido']['valor'] ?? 0;
        $nome_cliente = $_SESSION['ultimo_pedido']['cliente'] ?? '';
        $codigo_barras = $_SESSION['ultimo_pedido']['codigo_barras'] ?? '';
        $vencimento = $_SESSION['ultimo_pedido']['vencimento'] ?? '';
    }
}

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

        .info-vencimento {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }

        .linha-digitavel {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            letter-spacing: 1px;
            background: white;
            padding: 15px;
            border: 1px dashed #ccc;
            border-radius: 6px;
            margin: 10px 0;
        }

        /* Estilos do dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background: white;
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

        .user-info p {
            margin: 0;
            font-size: 0.9rem;
            color: #333;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            text-decoration: none;
            color: #333;
            transition: background 0.3s;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-item i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 5px 0;
        }

        .logout-btn {
            color: #dc3545;
        }

        .logout-btn:hover {
            background: #f8d7da;
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
                        <p>
                            Seja bem-vindo, 
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
                    <a href="./cadastro.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
                <?php endif; ?>
            </div>
        </div>
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
        
        <div class="info-vencimento">
            <h4><i class="fas fa-calendar-alt"></i> Data de Vencimento</h4>
            <p style="font-size: 1.3rem; font-weight: bold; color: #d63031;">
                <?php echo $vencimento; ?>
            </p>
            <p style="font-size: 0.9rem; color: #666;">
                (3 dias úteis a partir de hoje)
            </p>
        </div>
        
        <div class="linha-boleto">
            <span>Beneficiário:</span>
            <strong>Verseal Galeria de Arte LTDA</strong>
        </div>
        
        <div class="linha-boleto">
            <span>Pagador:</span>
            <strong><?php echo htmlspecialchars($nome_cliente); ?></strong>
        </div>
        
        <div class="linha-boleto">
            <span>Valor:</span>
            <strong>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></strong>
        </div>
        
        <div class="codigo-barras" id="codigo-barras">
            <?php echo $codigo_barras; ?>
        </div>
        
        <p><strong>Linha digitável:</strong></p>
        <div class="linha-digitavel" id="linha-digitavel">
            <?php 
            // Gerar linha digitável formatada corretamente
            $linha_digitavel = formatLinhaDigitavel($codigo_barras);
            echo $linha_digitavel;
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn-imprimir" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir Boleto
            </button>
            <button class="btn-imprimir" onclick="copiarCodigoBarras()">
                <i class="fas fa-copy"></i> Copiar Código
            </button>
            <button class="btn-imprimir" onclick="copiarLinhaDigitavel()">
                <i class="fas fa-copy"></i> Copiar Linha Digitável
            </button>
        </div>
    </div>

    <div class="instrucoes">
        <h3>Instruções de pagamento:</h3>
        <ol>
            <li>Imprima o boleto ou anote o código de barras/linha digitável</li>
            <li>Pague em qualquer banco, lotérica ou internet banking</li>
            <li>O prazo de vencimento é de <strong>3 dias úteis</strong> (até <?php echo $vencimento; ?>)</li>
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

function copiarLinhaDigitavel() {
    const linha = document.getElementById('linha-digitavel').textContent;
    navigator.clipboard.writeText(linha).then(() => {
        alert('Linha digitável copiada!');
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
                // Redirecionar para página de sucesso
                window.location.href = 'sucesso-compra.php';
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

// Dropdown do perfil
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
    if (empty($codigo_barras) || strlen($codigo_barras) !== 44) {
        // Se não tiver 44 dígitos, gerar um padrão válido
        $codigo_barras = generateCodigoBarras();
    }
    
    // Formatar no padrão: AAAAA.BBBBB BBBBB.CCCCC CCCCC.DDDDD D E FFFFFFFFFFFF
    $parte1 = substr($codigo_barras, 0, 4) . substr($codigo_barras, 19, 1);
    $parte2 = substr($codigo_barras, 20, 4) . substr($codigo_barras, 24, 1);
    $parte3 = substr($codigo_barras, 25, 4) . substr($codigo_barras, 29, 1);
    $parte4 = substr($codigo_barras, 30, 1);
    $parte5 = substr($codigo_barras, 31, 13);
    
    return $parte1 . '.' . 
           substr($codigo_barras, 4, 4) . ' ' .
           $parte2 . '.' . 
           substr($codigo_barras, 10, 4) . ' ' .
           $parte3 . '.' . 
           substr($codigo_barras, 15, 4) . ' ' .
           $parte4 . ' ' .
           $parte5;
}
?>