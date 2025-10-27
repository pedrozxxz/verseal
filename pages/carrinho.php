<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;

// Lista de produtos disponíveis (COMPLETA - 12 produtos)
$produtos = [
    1 => [
        "id" => 1,
        "img" => "../img/imagem2.png",
        "nome" => "Obra da Daniele",
        "artista" => "Daniele Oliveira",
        "preco" => 199.99,
        "desc" => "Desenho realizado por Stefani e Daniele, feito digitalmente e manualmente.",
        "dimensao" => "21 x 29,7cm (Manual) / 390cm x 522cm (Digital)",
        "tecnica" => "Técnica mista: digital e manual",
        "ano" => 2024,
        "material" => "Tinta acrílica e digital",
        "categoria" => ["manual", "digital", "colorido"]
    ],
    2 => [
        "id" => 2,
        "img" => "../img/imagem9.png",
        "nome" => "Obra da Stefani", 
        "artista" => "Stefani Correa",
        "preco" => 188.99,
        "desc" => "Desenho realizado com técnica mista.",
        "dimensao" => "42 x 59,4cm",
        "tecnica" => "Técnica mista",
        "ano" => 2024,
        "material" => "Nanquim e aquarela",
        "categoria" => ["manual", "colorido"]
    ],
    3 => [
        "id" => 3,
        "img" => "../img/imagem2.png",
        "nome" => "Obra Moderna",
        "artista" => "Daniele Oliveira",
        "preco" => 250.00,
        "desc" => "Arte contemporânea com técnicas inovadoras.",
        "dimensao" => "50 x 70cm",
        "tecnica" => "Pintura digital",
        "ano" => 2024,
        "material" => "Digital - alta resolução",
        "categoria" => ["digital", "colorido"]
    ],
    4 => [
        "id" => 4,
        "img" => "../img/imagem2.png",
        "nome" => "Paisagem Expressionista",
        "artista" => "Stefani Correa", 
        "preco" => 179.99,
        "desc" => "Paisagem com cores vibrantes e traços expressionistas",
        "dimensao" => "60 x 80cm",
        "tecnica" => "Expressionismo",
        "ano" => 2024,
        "material" => "Óleo sobre tela",
        "categoria" => ["manual", "colorido"]
    ],
    5 => [
        "id" => 5,
        "img" => "../img/imagem2.png",
        "nome" => "Abstração Colorida",
        "artista" => "Lucas Andrade",
        "preco" => 159.90,
        "desc" => "Obra abstrata com paleta de cores vibrantes",
        "dimensao" => "40 x 60cm",
        "tecnica" => "Abstração",
        "ano" => 2024,
        "material" => "Acrílica sobre tela",
        "categoria" => ["manual", "colorido"]
    ],
    6 => [
        "id" => 6,
        "img" => "../img/imagem2.png",
        "nome" => "Figura Humana",
        "artista" => "Mariana Santos",
        "preco" => 220.00,
        "desc" => "Estudo da figura humana em movimento",
        "dimensao" => "70 x 100cm",
        "tecnica" => "Figurativo",
        "ano" => 2024,
        "material" => "Carvão e pastel",
        "categoria" => ["manual", "preto e branco"]
    ],
    7 => [
        "id" => 7,
        "img" => "../img/imagem2.png",
        "nome" => "Natureza Morta",
        "artista" => "Rafael Costa",
        "preco" => 145.50,
        "desc" => "Natureza morta com elementos clássicos",
        "dimensao" => "50 x 70cm",
        "tecnica" => "Realismo",
        "ano" => 2024,
        "material" => "Óleo sobre tela",
        "categoria" => ["manual", "colorido"]
    ],
    8 => [
        "id" => 8,
        "img" => "../img/imagem2.png",
        "nome" => "Cidade Noturna",
        "artista" => "Camila Rocha",
        "preco" => 189.99,
        "desc" => "Panorama urbano noturno",
        "dimensao" => "80 x 120cm",
        "tecnica" => "Urban sketching",
        "ano" => 2024,
        "material" => "Tinta acrílica",
        "categoria" => ["manual", "colorido"]
    ],
    9 => [
        "id" => 9,
        "img" => "../img/imagem2.png",
        "nome" => "Abstração Minimalista",
        "artista" => "João Almeida",
        "preco" => 249.00,
        "desc" => "Obra minimalista com formas puras",
        "dimensao" => "60 x 60cm",
        "tecnica" => "Minimalismo",
        "ano" => 2024,
        "material" => "Acrílica sobre MDF",
        "categoria" => ["manual", "colorido"]
    ],
    10 => [
        "id" => 10,
        "img" => "../img/imagem2.png",
        "nome" => "Flores Silvestres",
        "artista" => "Bianca Freitas",
        "preco" => 120.00,
        "desc" => "Composição floral com cores suaves",
        "dimensao" => "40 x 50cm",
        "tecnica" => "Aquarela",
        "ano" => 2024,
        "material" => "Aquarela sobre papel",
        "categoria" => ["manual", "colorido"]
    ],
    11 => [
        "id" => 11,
        "img" => "../img/imagem2.png",
        "nome" => "Mar em Movimento",
        "artista" => "Felipe Duarte",
        "preco" => 199.90,
        "desc" => "Representação do movimento das ondas",
        "dimensao" => "90 x 120cm",
        "tecnica" => "Abstração lírica",
        "ano" => 2024,
        "material" => "Óleo sobre tela",
        "categoria" => ["manual", "colorido"]
    ],
    12 => [
        "id" => 12,
        "img" => "../img/imagem2.png",
        "nome" => "Retrato em Preto e Branco",
        "artista" => "Ana Clara",
        "preco" => 134.99,
        "desc" => "Retrato clássico em técnica monocromática",
        "dimensao" => "50 x 70cm",
        "tecnica" => "Realismo",
        "ano" => 2024,
        "material" => "Grafite e carvão",
        "categoria" => ["manual", "preto e branco"]
    ]
];

// Inicializar carrinho na sessão se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Se for uma requisição AJAX, retornar JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $acao = $_POST['acao'] ?? '';
    $item_id = intval($_POST['item_id'] ?? 0);

    if ($acao === 'adicionar' && isset($produtos[$item_id])) {
        $produto = $produtos[$item_id];
        $existe_no_carrinho = false;

        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id'] == $item_id) {
                $item['qtd'] += 1;
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
                'qtd' => 1,
                'desc' => $produto['desc'],
                'dimensao' => $produto['dimensao']
            ];
        }

        echo json_encode(['success' => true, 'message' => 'Produto adicionado ao carrinho!']);
    } elseif ($acao === 'remover') {
        $_SESSION['carrinho'] = array_filter($_SESSION['carrinho'], function ($item) use ($item_id) {
            return $item['id'] != $item_id;
        });
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
        echo json_encode(['success' => true, 'message' => 'Produto removido do carrinho!']);
    } elseif ($acao === 'atualizar_qtd') {
        $nova_qtd = intval($_POST['qtd'] ?? 1);
        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id'] == $item_id) {
                $item['qtd'] = max(1, $nova_qtd);
                break;
            }
        }
        echo json_encode(['success' => true, 'message' => 'Quantidade atualizada!']);
    }
    exit;
}

// Processar ações normais (não-AJAX) - fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $item_id = intval($_POST['item_id'] ?? 0);

    if ($acao === 'adicionar' && isset($produtos[$item_id])) {
        $produto = $produtos[$item_id];
        $existe_no_carrinho = false;

        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id'] == $item_id) {
                $item['qtd'] += 1;
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
                'qtd' => 1,
                'desc' => $produto['desc'],
                'dimensao' => $produto['dimensao']
            ];
        }
    } elseif ($acao === 'remover') {
        $_SESSION['carrinho'] = array_filter($_SESSION['carrinho'], function ($item) use ($item_id) {
            return $item['id'] != $item_id;
        });
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
    } elseif ($acao === 'atualizar_qtd') {
        $nova_qtd = intval($_POST['qtd'] ?? 1);
        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id'] == $item_id) {
                $item['qtd'] = max(1, $nova_qtd);
                break;
            }
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$carrinho = $_SESSION['carrinho'];
$total = array_sum(array_map(fn($item) => $item["preco"] * $item["qtd"], $carrinho));
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
    
    <a href="./carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
    
    <!-- Dropdown Perfil -->
    <div class="profile-dropdown">
      <a href="#" class="icon-link" id="profile-icon">
        <i class="fas fa-user"></i>
      </a>
      <div class="dropdown-content" id="profile-dropdown">
        <?php if ($usuarioLogado): ?>
          <div class="user-info">
            <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado); ?></span>!</p>
          </div>
          <div class="dropdown-divider"></div>
          <a href="./pages/perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
          <div class="dropdown-divider"></div>
          <a href="./pages/logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
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
                                <img src="<?php echo $item['img']; ?>" alt="<?php echo $item['nome']; ?>">
                                <div class="item-info">
                                    <p><strong><?php echo $item['nome']; ?></strong></p>
                                    <span class="preco-unitario">R$
                                        <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>

                                    <div class="controles-quantidade">
                                        <button type="button" class="btn-diminuir"
                                            onclick="alterarQuantidade(<?php echo $item['id']; ?>, -1)">-</button>
                                        <input type="number" class="quantidade-input" value="<?php echo $item['qtd']; ?>"
                                            min="1" onchange="atualizarQuantidade(<?php echo $item['id']; ?>, this.value)">
                                        <button type="button" class="btn-aumentar"
                                            onclick="alterarQuantidade(<?php echo $item['id']; ?>, 1)">+</button>
                                    </div>

                                    <div class="subtotal">
                                        Subtotal: R$ <span
                                            class="subtotal-valor"><?php echo number_format($item['preco'] * $item['qtd'], 2, ',', '.'); ?></span>
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
                                    <span class="resumo-qtd"><?php echo $item['qtd']; ?>x</span>
                                    <span class="resumo-preco">R$
                                        <?php echo number_format($item['preco'] * $item['qtd'], 2, ',', '.'); ?></span>
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
                        <img src="<?php echo $produto['img']; ?>" alt="<?php echo $produto['nome']; ?>">
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
    const fd = new FormData(); fd.append('acao','adicionar'); fd.append('item_id',id);
    fetch(window.location.href,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{ if(d.success) mostrarNotificacao(d.message,'success'); atualizarInterfaceCarrinho(); });
}

// Remover
function removerDoCarrinho(id){
    const fd = new FormData(); fd.append('acao','remover'); fd.append('item_id',id);
    fetch(window.location.href,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{ if(d.success) mostrarNotificacao(d.message,'info'); atualizarInterfaceCarrinho(); });
}

// Alterar quantidade
function atualizarQuantidade(id,qtd){
    const fd = new FormData(); fd.append('acao','atualizar_qtd'); fd.append('item_id',id); fd.append('qtd',qtd);
    fetch(window.location.href,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{ if(d.success) mostrarNotificacao(d.message,'success'); atualizarInterfaceCarrinho(); });
}

function alterarQuantidade(id, delta){
    const input = document.querySelector(`.item-card[data-item-id="${id}"] .quantidade-input`);
    if(!input) return;
    let qtd = parseInt(input.value)+delta;
    if(qtd<1) qtd=1; input.value=qtd;
    atualizarQuantidade(id,qtd);
}

// Dropdown perfil
document.addEventListener('DOMContentLoaded', function () {
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    if(profileIcon && profileDropdown){
        profileIcon.addEventListener('click', e=>{ e.preventDefault(); e.stopPropagation(); profileDropdown.style.display=(profileDropdown.style.display==='block'?'none':'block'); });
        document.addEventListener('click', e=>{ if(!profileDropdown.contains(e.target) && e.target!==profileIcon) profileDropdown.style.display='none'; });
        profileDropdown.addEventListener('click', e=>{ e.stopPropagation(); });
    }
});
</script>
</body>

</html>