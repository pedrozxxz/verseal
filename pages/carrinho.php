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

// Verifica se há sessão de cliente (corrigido para "clientes" no plural)
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
}
// Verifica se há sessão de artista (corrigido para "artistas" no plural)
elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}

// Busca as obras cadastradas no banco
$sql = "SELECT id, nome, artista, preco, descricao AS descricao, dimensoes, tecnica, ano, material, imagem_url, categorias 
        FROM produtos";
$result = $conexao->query($sql);

// Cria array com as obras
$produtos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // CORREÇÃO: Processar corretamente a URL da imagem
        $imagem_url = '';
        if (!empty($row['imagem_url'])) {
            // Se a imagem já tem o caminho completo, usar como está
            if (strpos($row['imagem_url'], '../') === 0) {
                $imagem_url = $row['imagem_url'];
            } 
            // Se é um caminho relativo sem ../, adicionar ../
            elseif (strpos($row['imagem_url'], 'img/') === 0) {
                $imagem_url = '../' . $row['imagem_url'];
            }
            // Se é um caminho de upload, adicionar ../
            elseif (strpos($row['imagem_url'], 'uploads/') === 0) {
                $imagem_url = '../' . $row['imagem_url'];
            }
            // Se já começa com img/uploads/, adicionar ../
            elseif (strpos($row['imagem_url'], 'img/uploads/') === 0) {
                $imagem_url = '../' . $row['imagem_url'];
            }
            // Para qualquer outro caso, usar como está
            else {
                $imagem_url = $row['imagem_url'];
            }
        } else {
            // Imagem padrão se não houver imagem
            $imagem_url = '../img/imagem2.png';
        }

        $produtos[$row["id"]] = [
            "id" => $row["id"],
            "img" => $imagem_url, // CORRIGIDO: Usar a URL processada
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
    $produtos = []; // Nenhuma obra cadastrada
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
                'dimensao' => $produto['dimensao']
            ];
            
            // 🔹 ADICIONAR NOTIFICAÇÃO AO ADICIONAR PRODUTO
            $_SESSION['carrinho_notificacoes'][$item_id] = [
                'nome' => $produto['nome'],
                'timestamp' => time()
            ];
        }

        echo json_encode(['success' => true, 'message' => 'Produto adicionado ao carrinho!']);
    } elseif ($acao === 'remover') {
        $_SESSION['carrinho'] = array_filter($_SESSION['carrinho'], function ($item) use ($item_id) {
            return $item['id'] != $item_id;
        });
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
        echo json_encode(['success' => true, 'message' => 'Produto removido do carrinho!']);
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
                'dimensao' => $produto['dimensao']
            ];
            
            // 🔹 ADICIONAR NOTIFICAÇÃO AO ADICIONAR PRODUTO
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/carrinho.css">
    <style>
        /* 🔹 SISTEMA DE NOTIFICAÇÕES - INÍCIO */
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

        .notificacao-item:last-child {
            margin-bottom: 0;
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
        /* 🔹 SISTEMA DE NOTIFICAÇÕES - FIM */

        /* Estilo para imagens que não carregam */
        .item-card img,
        .produto-card img {
            object-fit: cover;
            height: 200px;
            width: 100%;
        }

        .img-error {
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .item-card img {
            height: 150px;
            width: 150px;
            border-radius: 8px;
        }

        .produto-card img {
            height: 250px;
        }
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
      <?php endif; ?>

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

    <!-- Menu Hamburguer Flutuante -->
    <div class="hamburger-menu-desktop">
      <input type="checkbox" id="menu-toggle-desktop">
      <label for="menu-toggle-desktop" class="hamburger-desktop">
        <i class="fas fa-bars"></i>
        <span>ACESSO</span>
      </label>
      <div class="menu-content-desktop">
        <div class="menu-section">
          <a href="../index.php" class="menu-item" onclick="document.getElementById('menu-toggle-desktop').checked = false;">
            <i class="fas fa-user"></i> <span>Cliente</span>
          </a>
          <a href="./admhome.php" class="menu-item"><i class="fas fa-user-shield"></i> <span>ADM</span></a>
          <a href="./artistahome.php" class="menu-item"><i class="fas fa-palette"></i> <span>Artista</span></a>
        </div>
      </div>
    </div>
  </nav>
</header>

    <!-- CONTEÚDO -->
    <main class="pagina-carrinho">
        <h1 class="titulo-pagina">Carrinho</h1>

        <!-- 🔹 SEÇÃO DE NOTIFICAÇÕES -->
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
                                    <span class="preco-unitario">R$
                                        <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>

                                    <div class="subtotal">
                                        Subtotal: R$ <span
                                            class="subtotal-valor"><?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                                    </div>

                                    <button type="button" class="btn-remover"
                                        onclick="removerDoCarrinho(<?php echo $item['id']; ?>)">
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
                                    <span class="resumo-preco">R$
                                        <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
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
                            <span class="preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                            <p class="descricao"><?php echo $produto['desc']; ?></p>
                            <button type="button" class="btn-adicionar"
                                onclick="adicionarAoCarrinho(<?php echo $produto['id']; ?>)">
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
// 🔹 SISTEMA DE NOTIFICAÇÕES - INÍCIO
// Atualizar badge do carrinho
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

// Remover notificação individual
function removerNotificacao(produtoId) {
    window.location.href = `carrinho.php?remover_notificacao=1&produto_id=${produtoId}`;
}

// Limpar todas as notificações
function limparTodasNotificacoes() {
    window.location.href = 'carrinho.php?limpar_notificacoes=1';
}
// 🔹 SISTEMA DE NOTIFICAÇÕES - FIM

// Notificação
function mostrarNotificacao(mensagem, tipo='success') {
    const n = document.getElementById('notificacao');
    n.textContent = mensagem; n.className = `notificacao ${tipo}`; n.style.display='block';
    setTimeout(()=>n.style.display='none',3000);
}

// Atualizar carrinho via AJAX
function atualizarInterfaceCarrinho() {
    fetch(window.location.href)
    .then(r=>r.text())
    .then(html=>{
        const doc = new DOMParser().parseFromString(html,'text/html');
        const novo = doc.getElementById('carrinho-container');
        if(novo) document.getElementById('carrinho-container').innerHTML = novo.innerHTML;
        // Atualizar badge após atualizar interface
        atualizarBadgeCarrinho();
    }).catch(e=>window.location.reload());
}

function finalizarCompra() {
    // Verifica se o carrinho está vazio
    const carrinhoVazio = document.querySelectorAll('.item-card').length === 0;
    if (carrinhoVazio) {
        mostrarNotificacao('Seu carrinho está vazio!', 'error');
        return;
    }

    // Redireciona para a página de pagamento (exemplo)
    window.location.href = './checkout.php';
}

// Adicionar
function adicionarAoCarrinho(id){
    const fd = new FormData(); 
    fd.append('acao','adicionar'); 
    fd.append('item_id',id);
    
    fetch(window.location.href,{
        method:'POST',
        body:fd,
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r=>r.json())
    .then(d=>{ 
        if(d.success) {
            mostrarNotificacao(d.message,'success'); 
            atualizarInterfaceCarrinho();
        }
    });
}

// Remover
function removerDoCarrinho(id){
    const fd = new FormData(); 
    fd.append('acao','remover'); 
    fd.append('item_id',id);
    
    fetch(window.location.href,{
        method:'POST',
        body:fd,
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r=>r.json())
    .then(d=>{ 
        if(d.success) {
            mostrarNotificacao(d.message,'info'); 
            atualizarInterfaceCarrinho();
        }
    });
}

// Dropdown perfil
document.addEventListener('DOMContentLoaded', function () {
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    if(profileIcon && profileDropdown){
        profileIcon.addEventListener('click', e=>{ 
            e.preventDefault(); 
            e.stopPropagation(); 
            profileDropdown.style.display=(profileDropdown.style.display==='block'?'none':'block'); 
        });
        document.addEventListener('click', e=>{ 
            if(!profileDropdown.contains(e.target) && e.target!==profileIcon) 
                profileDropdown.style.display='none'; 
        });
        profileDropdown.addEventListener('click', e=>{ 
            e.stopPropagation(); 
        });
    }
    
    // Atualizar badge quando a página carregar
    atualizarBadgeCarrinho();
});
</script>
</body>
</html>