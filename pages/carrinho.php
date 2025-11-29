<?php
session_start();

// 🔹 SISTEMA DE NOTIFICAÇÕES - INÍCIO
if (!isset($_SESSION['carrinho_notificacoes'])) {
    $_SESSION['carrinho_notificacoes'] = [];
}

// Processar remoção de notificação
if (isset($_GET['remover_notificacao']) && isset($_GET['produto_id'])) {
    $produto_id = intval($_GET['produto_id']);
    if (isset($_SESSION['carrinho_notificacoes'][$produto_id])) {
        unset($_SESSION['carrinho_notificacoes'][$produto_id]);
    }
    header('Location: carrinho.php');
    exit;
}

// Processar limpeza de todas as notificações
if (isset($_GET['limpar_notificacoes'])) {
    $_SESSION['carrinho_notificacoes'] = [];
    header('Location: carrinho.php');
    exit;
}
// 🔹 SISTEMA DE NOTIFICAÇÕES - FIM

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "verseal";

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

// Opcional: define charset
$conexao->set_charset("utf8mb4");

$caminhoConexao = file_exists("../conexao.php") ? "../conexao.php" : "conexao.php";
require_once $caminhoConexao;

// 🔹 Garante que a variável $conn tenha a conexão válida
if (isset($conexao) && $conexao instanceof mysqli) {
    $conn = $conexao;
} else {
    die("❌ Erro: conexão com o banco de dados não foi estabelecida.");
}

// Verificar se usuário está logado (cliente ou artista)
$usuarioLogado = null;
$tipoUsuario = null;
$usuario_id = null;

// Verifica se há sessão de cliente
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
    $usuario_id = $usuarioLogado['id'];
}
// Verifica se há sessão de artista
elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
    $usuario_id = $usuarioLogado['id'];
}

$sql = "
SELECT p.id, p.nome, p.artista, p.preco, p.descricao AS descricao, 
       p.dimensoes, p.tecnica, p.ano, p.material, p.imagem_url, p.categorias
FROM produtos p
LEFT JOIN itens_pedido ip ON ip.produto_id = p.id
WHERE ip.produto_id IS NULL
";

$result = $conexao->query($sql);

// Cria array com as obras
$produtos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Processar corretamente a URL da imagem
        $imagem_url = '';
        if (!empty($row['imagem_url'])) {
            if (strpos($row['imagem_url'], '../') === 0) {
                $imagem_url = $row['imagem_url'];
            } elseif (strpos($row['imagem_url'], 'img/') === 0) {
                $imagem_url = '../' . $row['imagem_url'];
            } elseif (strpos($row['imagem_url'], 'uploads/') === 0) {
                $imagem_url = '../' . $row['imagem_url'];
            } elseif (strpos($row['imagem_url'], 'img/uploads/') === 0) {
                $imagem_url = '../' . $row['imagem_url'];
            } else {
                $imagem_url = $row['imagem_url'];
            }
        } else {
            $imagem_url = '../img/imagem2.png';
        }

        $produtos[$row["id"]] = [
            "id" => $row["id"],
            "img" => $imagem_url,
            "nome" => $row["nome"],
            "artista" => $row["artista"],
            "preco" => (float)$row["preco"],
            "desc" => $row["descricao"],
            "dimensao" => $row["dimensoes"],
            "tecnica" => $row["tecnica"],
            "ano" => $row["ano"],
            "material" => $row["material"],
            "categoria" => explode(",", $row["categorias"])
        ];
    }
} else {
    $produtos = [];
}

// 🔹 Inicializa o carrinho na sessão, se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// 🔹 Requisição AJAX (adicionar, remover, atualizar)
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $acao = $_POST['acao'] ?? '';
    $item_id = intval($_POST['item_id'] ?? 0);

    if ($acao === 'adicionar' && isset($produtos[$item_id])) {
        $produto = $produtos[$item_id];
        $existe_no_carrinho = false;

        // Verificar se já existe no carrinho (sessão)
        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id'] == $item_id) {
                $existe_no_carrinho = true;
                break;
            }
        }

        if (!$existe_no_carrinho) {
            $_SESSION['carrinho'][] = [
                'id' => $produto['id'],
                'img' => $produto['img'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'desc' => $produto['desc'],
                'dimensao' => $produto['dimensao'],
                'artista' => $produto['artista']
            ];
            
            // 🔹 ADICIONAR NOTIFICAÇÃO
            $_SESSION['carrinho_notificacoes'][$item_id] = [
                'nome' => $produto['nome'],
                'timestamp' => time()
            ];
            
            echo json_encode(['success' => true, 'message' => 'Obra adicionada ao carrinho!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Esta obra já está no seu carrinho!']);
        }
        
    } elseif ($acao === 'remover') {
        // Remover da sessão
        $_SESSION['carrinho'] = array_filter($_SESSION['carrinho'], function ($item) use ($item_id) {
            return $item['id'] != $item_id;
        });
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
        
        echo json_encode(['success' => true, 'message' => 'Obra removida do carrinho!']);
    }
    exit;
}

// 🔹 Processar ações normais (sem AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $item_id = intval($_POST['item_id'] ?? 0);

    if ($acao === 'adicionar' && isset($produtos[$item_id])) {
        $produto = $produtos[$item_id];
        $existe_no_carrinho = false;

        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id'] == $item_id) {
                $existe_no_carrinho = true;
                break;
            }
        }

        if (!$existe_no_carrinho) {
            $_SESSION['carrinho'][] = [
                'id' => $produto['id'],
                'img' => $produto['img'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'desc' => $produto['desc'],
                'dimensao' => $produto['dimensao'],
                'artista' => $produto['artista']
            ];
            
            // 🔹 ADICIONAR NOTIFICAÇÃO
            $_SESSION['carrinho_notificacoes'][$item_id] = [
                'nome' => $produto['nome'],
                'timestamp' => time()
            ];
        }
        
    } elseif ($acao === 'remover') {
        $_SESSION['carrinho'] = array_filter($_SESSION['carrinho'], function ($item) use ($item_id) {
            return $item['id'] != $item_id;
        });
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 🔹 Calcula o total do carrinho
$carrinho = $_SESSION['carrinho'];
$total = array_sum(array_map(fn($item) => $item["preco"], $carrinho));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verseal - Carrinho</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/carrinho.css">
    <style>
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
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .lista-notificacoes {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #cc624e;
        }

        .notificacao-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .notificacao-info {
            flex: 1;
        }

        .notificacao-nome {
            font-weight: bold;
            color: #333;
        }

        .notificacao-tempo {
            font-size: 0.8rem;
            color: #666;
        }

        .btn-remover-notificacao {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .btn-remover-notificacao:hover {
            background: #c0392b;
        }

        .btn-limpar-todas {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-limpar-todas:hover {
            background: #7f8c8d;
        }

        .artista {
            font-style: italic;
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
        }

        .item-card img,
        .produto-card img {
            object-fit: cover;
        }

        .img-error {
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .item-card img {
            height: 150px;
            width: 150px;
            border-radius: 8px;
        }

        .produto-card img {
            height: 250px;
        }

        /* Notificação estilo */
        .notificacao {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .notificacao.success { background: #4CAF50; }
        .notificacao.error { background: #f44336; }
        .notificacao.warning { background: #ff9800; }
        .notificacao.info { background: #f44336; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="logo">Verseal</div>
        <nav>
            <a href="../index.php">Início</a>
            <a href="./produto.php">Obras</a>
            <a href="./sobre.php">Sobre</a>
            <a href="./artistas.php">Artistas</a>
            <a href="./contato.php">Contato</a>
            
            <!-- ÍCONE DO CARRINHO COM NOTIFICAÇÃO -->
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
            
            <!-- Dropdown Perfil -->
          <div class="profile-dropdown">
  <a href="#" class="icon-link" id="profile-icon">
    <i class="fas fa-user"></i>
  </a>
  <div class="dropdown-content" id="profile-dropdown">
    <?php if ($usuarioLogado): ?>
      <div class="user-info">
        <p>
          Seja bem-vindo, 
          <span id="user-name">
            <?php 
            if ($tipoUsuario === "cliente") {
              echo htmlspecialchars($usuarioLogado['nome']);
            } elseif ($tipoUsuario === "artista") {
              echo htmlspecialchars($usuarioLogado['nome_artistico']);
            }
            ?>
          </span>!
        </p>
      </div>
      <div class="dropdown-divider"></div>

      <?php if ($tipoUsuario === "cliente"): ?>
        <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
      <?php elseif ($tipoUsuario === "artista"): ?>
        <a href="./artistahome.php" class="dropdown-item"><i class="fas fa-palette"></i> Meu Perfil</a>
      <?php endif; ?>

      <div class="dropdown-divider"></div>
      <a href="logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>

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

    <!-- CONTEÚDO -->
    <main class="pagina-carrinho">
        <h1 class="titulo-pagina">Carrinho de Obras</h1>

        <!-- SEÇÃO DE NOTIFICAÇÕES -->
        <?php if (!empty($_SESSION['carrinho_notificacoes'])): ?>
        <div class="lista-notificacoes">
            <h3><i class="fas fa-bell"></i> Notificações Recentes</h3>
            <?php foreach ($_SESSION['carrinho_notificacoes'] as $produto_id => $notificacao): ?>
            <div class="notificacao-item">
                <div class="notificacao-info">
                    <div class="notificacao-nome"><?php echo htmlspecialchars($notificacao['nome']); ?></div>
                    <div class="notificacao-tempo">Adicionado há <?php echo time() - $notificacao['timestamp']; ?> segundos</div>
                </div>
                <button class="btn-remover-notificacao" onclick="removerNotificacao(<?php echo $produto_id; ?>)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endforeach; ?>
            <button class="btn-limpar-todas" onclick="limparTodasNotificacoes()">
                <i class="fas fa-trash"></i> Limpar Todas
            </button>
        </div>
        <?php endif; ?>

        <!-- NOTIFICAÇÃO -->
        <div id="notificacao" class="notificacao"></div>

        <div id="carrinho-container">
            <?php if (empty($carrinho)): ?>
                <div class="carrinho-vazio">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Seu carrinho está vazio</h2>
                    <p>Confira nossas sugestões de obras abaixo!</p>
                </div>
            <?php else: ?>
                <div class="carrinho-conteudo">
                    <!-- LISTA DE ITENS -->
                    <div class="carrinho-itens">
                        <?php foreach ($carrinho as $item): ?>
                            <div class="item-card" data-item-id="<?php echo $item['id']; ?>">
                                <img src="<?php echo $item['img']; ?>" 
                                     alt="<?php echo $item['nome']; ?>"
                                     onerror="this.onerror=null; this.src='../img/imagem2.png'; this.classList.add('img-error');">
                                <div class="item-info">
                                    <p><strong><?php echo $item['nome']; ?></strong></p>
                                    <?php if (!empty($item['artista'])): ?>
                                        <p class="artista">por <?php echo $item['artista']; ?></p>
                                    <?php endif; ?>
                                    <span class="preco-unitario">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>

                                    <div class="subtotal">
                                        Preço: R$ <span class="subtotal-valor"><?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                                    </div>

                                    <button type="button" class="btn-remover" onclick="removerDoCarrinho(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i> Remover
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- RESUMO -->
                    <div class="carrinho-resumo">
                        <h3>Resumo do Pedido</h3>

                        <div class="resumo-itens">
                            <?php foreach ($carrinho as $item): ?>
                                <div class="resumo-item">
                                    <span class="resumo-nome"><?php echo $item['nome']; ?></span>
                                    <span class="resumo-preco">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="total">
                            <span>Total</span>
                            <strong id="total-geral">R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                        </div>

                        <?php if ($usuarioLogado): ?>
                            <button class="btn-comprar" onclick="finalizarCompra()">
                                <i class="fas fa-credit-card"></i> Finalizar Compra
                            </button>
                        <?php else: ?>
                            <div class="aviso-login-carrinho">
                                <div class="aviso-conteudo">
                                    <i class="fas fa-user-lock"></i>
                                    <div class="aviso-texto">
                                        <h4>Login Necessário</h4>
                                        <p>Para finalizar sua compra, faça login ou cadastre-se</p>
                                    </div>
                                </div>
                                <div class="botoes-acao">
                                    <a href="login.php?from=checkout" class="btn-login-carrinho">
                                        <i class="fas fa-sign-in-alt"></i> Fazer Login
                                    </a>
                                    <a href="login.php?from=checkout" class="btn-cadastro-carrinho">
                                        <i class="fas fa-user-plus"></i> Cadastrar
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="./produto.php" class="btn-continuar-comprando">Continuar Comprando</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- SEÇÃO DE SUGESTÕES DE OBRAS -->
        <section class="sugestoes-obras">
            <h2>Sugestão de Obras</h2>
            <div class="lista-produtos">
                <?php foreach ($produtos as $produto): ?>
                    <div class="produto-card">
                        <img src="<?php echo $produto['img']; ?>" 
                             alt="<?php echo $produto['nome']; ?>"
                             onerror="this.onerror=null; this.src='../img/imagem2.png'; this.classList.add('img-error');">
                        <div class="produto-info">
                            <h3><?php echo $produto['nome']; ?></h3>
                            <?php if (!empty($produto['artista'])): ?>
                                <p class="artista">por <?php echo $produto['artista']; ?></p>
                            <?php endif; ?>
                            <span class="preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                            <p class="descricao"><?php echo substr($produto['desc'], 0, 100) . '...'; ?></p>
                            <button type="button" class="btn-adicionar" onclick="adicionarAoCarrinho(<?php echo $produto['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
        <div class="social">
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-whatsapp"></i></a>
        </div>
    </footer>

    <script>
// 🔹 SISTEMA DE NOTIFICAÇÕES
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

function removerNotificacao(produtoId) {
    window.location.href = 'carrinho.php?remover_notificacao=1&produto_id=' + produtoId;
}

function limparTodasNotificacoes() {
    window.location.href = 'carrinho.php?limpar_notificacoes=1';
}

// 🔹 NOTIFICAÇÃO
function mostrarNotificacao(mensagem, tipo = 'success') {
    const notificacao = document.getElementById('notificacao');
    notificacao.textContent = mensagem;
    notificacao.className = 'notificacao ' + tipo;
    notificacao.style.display = 'block';
    
    setTimeout(function() {
        notificacao.style.display = 'none';
    }, 3000);
}

// 🔹 ATUALIZAR CARRINHO
function atualizarInterfaceCarrinho() {
    // Recarrega a página para atualizar o carrinho
    window.location.reload();
}

// 🔹 FINALIZAR COMPRA
function finalizarCompra() {
    const carrinhoVazio = <?php echo empty($carrinho) ? 'true' : 'false'; ?>;
    
    if (carrinhoVazio) {
        mostrarNotificacao('Seu carrinho está vazio!', 'error');
        return;
    }

    // Redireciona para a página de pagamento
    window.location.href = './checkout.php';
}

// 🔹 ADICIONAR AO CARRINHO
function adicionarAoCarrinho(id) {
    console.log('Adicionando produto:', id);
    
    const formData = new FormData();
    formData.append('acao', 'adicionar');
    formData.append('item_id', id);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacao(data.message, 'success');
            // Atualiza a interface após um breve delay
            setTimeout(function() {
                atualizarInterfaceCarrinho();
            }, 1000);
        } else {
            mostrarNotificacao(data.message, 'warning');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarNotificacao('Erro ao adicionar obra', 'error');
    });
}

// 🔹 REMOVER DO CARRINHO
function removerDoCarrinho(id) {
    console.log('Removendo produto:', id);
    
    const formData = new FormData();
    formData.append('acao', 'remover');
    formData.append('item_id', id);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacao(data.message, 'info');
            // Atualiza a interface após um breve delay
            setTimeout(function() {
                atualizarInterfaceCarrinho();
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarNotificacao('Erro ao remover obra', 'error');
    });
}

// 🔹 DROPDOWN PERFIL
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado - Carrinho');
    
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (profileDropdown.style.display === 'block') {
                profileDropdown.style.display = 'none';
            } else {
                profileDropdown.style.display = 'block';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
                profileDropdown.style.display = 'none';
            }
        });
    }
    
    // Atualizar badge quando a página carregar
    atualizarBadgeCarrinho();
    
    // Debug: verificar se os botões estão funcionando
    const botoesAdicionar = document.querySelectorAll('.btn-adicionar');
    const botoesRemover = document.querySelectorAll('.btn-remover');
    
    console.log('Botões adicionar:', botoesAdicionar.length);
    console.log('Botões remover:', botoesRemover.length);
    
    botoesAdicionar.forEach(botao => {
        botao.addEventListener('click', function() {
            console.log('Botão adicionar clicado');
        });
    });
    
    botoesRemover.forEach(botao => {
        botao.addEventListener('click', function() {
            console.log('Botão remover clicado');
        });
    });
});
    </script>
</body>
</html>