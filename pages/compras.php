<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Carregar dados do usuário
$usuarioLogado = $_SESSION["usuario"];
$usuario_id = $usuarioLogado['id'] ?? null;

if (!$usuario_id) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Buscar pedidos do usuário
$sql_pedidos = "SELECT p.*, 
                COUNT(ip.id) as total_itens,
                SUM(ip.preco) as valor_calculado
                FROM pedidos p
                LEFT JOIN itens_pedido ip ON p.id = ip.pedido_id
                WHERE p.usuario_id = ?
                GROUP BY p.id
                ORDER BY p.data_pedido DESC";

$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("i", $usuario_id);
$stmt_pedidos->execute();
$result_pedidos = $stmt_pedidos->get_result();
$pedidos = $result_pedidos->fetch_all(MYSQLI_ASSOC);

// Função para formatar status
function formatarStatus($status) {
    $status_map = [
        'pendente' => ['text' => 'Pendente', 'class' => 'status-pendente'],
        'pago' => ['text' => 'Pago', 'class' => 'status-pago'],
        'enviado' => ['text' => 'Enviado', 'class' => 'status-enviado'],
        'entregue' => ['text' => 'Entregue', 'class' => 'status-entregue'],
        'cancelado' => ['text' => 'Cancelado', 'class' => 'status-cancelado']
    ];
    
    return $status_map[$status] ?? ['text' => ucfirst($status), 'class' => 'status-pendente'];
}

// Função para formatar método de pagamento
function formatarMetodoPagamento($metodo) {
    $metodos = [
        'pix' => 'PIX',
        'cartao_credito' => 'Cartão de Crédito',
        'cartao_debito' => 'Cartão de Débito',
        'boleto' => 'Boleto Bancário'
    ];
    
    return $metodos[$metodo] ?? ucfirst($metodo);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minhas Compras - Verseal</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/perfil.css">
<style>
/* Estilos para a página de compras */
.pagina-compras {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.titulo-pagina {
    text-align: center;
    margin-bottom: 40px;
}

.titulo-pagina h1 {
    font-family: 'Playfair Display', serif;
    color: #333;
    margin-bottom: 10px;
}

.titulo-pagina p {
    color: #666;
    font-size: 1.1rem;
}

.container-compras {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 30px;
}

/* Card de pedido */
.card-pedido {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #e07b67;
}

.header-pedido {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.info-pedido {
    flex: 1;
}

.codigo-pedido {
    font-weight: bold;
    color: #333;
    font-size: 1.1rem;
}

.data-pedido {
    color: #666;
    font-size: 0.9rem;
}

.status-pedido {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.status-pendente { background: #fff3cd; color: #856404; }
.status-pago { background: #d1ecf1; color: #0c5460; }
.status-enviado { background: #d4edda; color: #155724; }
.status-entregue { background: #e2e3e5; color: #383d41; }
.status-cancelado { background: #f8d7da; color: #721c24; }

.detalhes-pedido {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.info-pagamento, .info-entrega {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.info-pagamento h4, .info-entrega h4 {
    margin-bottom: 10px;
    color: #333;
    font-size: 0.9rem;
}

.metodo-pagamento {
    font-weight: bold;
    color: #e07b67;
}

.itens-pedido {
    margin-top: 20px;
}

.lista-itens {
    display: grid;
    gap: 15px;
}

.item-pedido {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}
/* 🔹 SISTEMA DE NOTIFICAÇÕES - CORRIGIDO */
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
    transition: all 0.3s ease;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.badge-bounce {
    animation: bounce 0.5s ease;
}

@keyframes bounce {
    0%, 20%, 60%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    80% { transform: translateY(-5px); }
}

/* 🔹 TAMANHO DAS OBRAS AUMENTADO */
.imagem-item {
    width: 120px; /* Aumentado de 60px */
    height: 120px; /* Aumentado de 60px */
    border-radius: 8px;
    overflow: hidden;
}

.imagem-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* 🔹 AJUSTES NO LAYOUT PARA AS IMAGENS MAIORES */
.item-pedido {
    display: flex;
    align-items: center;
    gap: 20px; /* Aumentado de 15px */
    padding: 20px; /* Aumentado de 15px */
    background: #f8f9fa;
    border-radius: 8px;
}

.info-item {
    flex: 1;
}

.nome-item {
    font-weight: bold;
    color: #333;
    margin-bottom: 8px; /* Aumentado de 5px */
    font-size: 1.1rem; /* Aumentado */
}

.artista-item {
    color: #666;
    font-size: 1rem; /* Aumentado de 0.9rem */
}

.preco-item {
    font-weight: bold;
    color: #e07b67;
    font-size: 1.1rem; /* Aumentado */
}

/* 🔹 AJUSTES RESPONSIVOS PARA AS IMAGENS MAIORES */
@media (max-width: 768px) {
    .imagem-item {
        width: 100px; /* Aumentado para mobile */
        height: 100px; /* Aumentado para mobile */
    }
    
    .item-pedido {
        gap: 15px;
        padding: 15px;
    }
    
    .nome-item {
        font-size: 1rem;
    }
    
    .artista-item {
        font-size: 0.9rem;
    }
}

/* 🔹 ESTILOS EXISTENTES DA PÁGINA DE COMPRAS (mantidos) */
.pagina-compras {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.titulo-pagina {
    text-align: center;
    margin-bottom: 40px;
}

.titulo-pagina h1 {
    font-family: 'Playfair Display', serif;
    margin-bottom: 10px;
    color: #cc624e;
}

.titulo-pagina p {
    color: #666;
    font-size: 1.1rem;
}

.container-compras {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 30px;
}

/* Card de pedido */
.card-pedido {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #e07b67;
}

.header-pedido {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.info-pedido {
    flex: 1;
}

.codigo-pedido {
    font-weight: bold;
    color: #333;
    font-size: 1.1rem;
}

.data-pedido {
    color: #666;
    font-size: 0.9rem;
}

.status-pedido {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.status-pendente { background: #fff3cd; color: #856404; }
.status-pago { background: #d1ecf1; color: #0c5460; }
.status-enviado { background: #d4edda; color: #155724; }
.status-entregue { background: #e2e3e5; color: #383d41; }
.status-cancelado { background: #f8d7da; color: #721c24; }

.detalhes-pedido {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.info-pagamento, .info-entrega {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.info-pagamento h4, .info-entrega h4 {
    margin-bottom: 10px;
    color: #333;
    font-size: 0.9rem;
}

.metodo-pagamento {
    font-weight: bold;
    color: #e07b67;
}

.itens-pedido {
    margin-top: 20px;
}

.lista-itens {
    display: grid;
    gap: 15px;
}

.total-pedido {
    text-align: right;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.valor-total {
    font-size: 1.2rem;
    font-weight: bold;
    color: #333;
}

.sem-compras {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.sem-compras i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.sem-compras h3 {
    margin-bottom: 10px;
    color: #333;
}

.btn-comprar {
    display: inline-block;
    background: #e07b67;
    color: white;
    padding: 12px 30px;
    border-radius: 5px;
    text-decoration: none;
    margin-top: 20px;
    transition: background 0.3s ease;
}

.btn-comprar:hover {
    background: #cc624e;
}

/* Responsivo */
@media (max-width: 768px) {
    .container-compras {
        grid-template-columns: 1fr;
    }
    
    .detalhes-pedido {
        grid-template-columns: 1fr;
    }
    
    .header-pedido {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .item-pedido {
        flex-direction: column;
        text-align: center;
    }
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

    <div class="notificacao-carrinho">
        <a href="./carrinho.php" class="icon-link">
            <i class="fas fa-shopping-cart"></i>
            <span class="carrinho-badge" id="carrinhoBadge">
                <?php
                $total_notificacoes = count($_SESSION['carrinho_notificacoes'] ?? []);
                if ($total_notificacoes > 0) {
                    echo $total_notificacoes;
                }
                ?>
            </span>
        </a>
    </div>

    <div class="profile-dropdown">
      <a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
      <div class="dropdown-content" id="profile-dropdown">
        <?php if (isset($usuarioLogado)): ?>
          <div class="user-info">
            <p>Seja bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>!</p>
          </div>
          <div class="dropdown-divider"></div>
          <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
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
  </nav>
</header>

<main class="pagina-compras">
    <div class="titulo-pagina">
        <h1>Minhas Compras</h1>
        <p>Acompanhe seus pedidos e histórico de compras</p>
    </div>

    <div class="container-compras">
        <div class="menu-lateral">
            <div class="info-usuario">
                <div class="avatar">
                    <?php if (!empty($usuarioLogado['foto_perfil'])): ?>
                        <img src="../uploads/usuarios/<?php echo htmlspecialchars($usuarioLogado['foto_perfil']); ?>" alt="Foto de perfil" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #e07b67, #cc624e); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($usuarioLogado['nome']); ?></h3>
                <p>Membro desde <?php echo date('m/Y', strtotime($usuarioLogado['data_cadastro'] ?? 'now')); ?></p>
            </div>

            <ul class="menu-links">
                <li><a href="perfil.php"><i class="fas fa-user-circle"></i> Meu Perfil</a></li>
                <li><a href="compras.php" class="ativo"><i class="fas fa-shopping-bag"></i> Minhas Compras</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>

        <div class="conteudo-principal">
            <?php if (empty($pedidos)): ?>
                <div class="sem-compras">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Nenhuma compra encontrada</h3>
                    <p>Você ainda não realizou nenhuma compra em nossa loja.</p>
                    <a href="./produto.php" class="btn-comprar">Explorar Obras</a>
                </div>
            <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <?php
                    // Buscar itens deste pedido
                    $sql_itens = "SELECT ip.*, pr.nome, pr.artista, pr.imagem_url 
                                 FROM itens_pedido ip 
                                 JOIN produtos pr ON ip.produto_id = pr.id 
                                 WHERE ip.pedido_id = ?";
                    $stmt_itens = $conn->prepare($sql_itens);
                    $stmt_itens->bind_param("i", $pedido['id']);
                    $stmt_itens->execute();
                    $result_itens = $stmt_itens->get_result();
                    $itens = $result_itens->fetch_all(MYSQLI_ASSOC);
                    
                    $status_info = formatarStatus($pedido['status']);
                    ?>
                    
                    <div class="card-pedido">
                        <div class="header-pedido">
                            <div class="info-pedido">
                                <div class="codigo-pedido">Pedido #<?php echo htmlspecialchars($pedido['codigo_pedido']); ?></div>
                                <div class="data-pedido">Realizado em: <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></div>
                            </div>
                            <div class="status-pedido <?php echo $status_info['class']; ?>">
                                <?php echo $status_info['text']; ?>
                            </div>
                        </div>

                        <div class="detalhes-pedido">
                            <div class="info-pagamento">
                                <h4>Informações de Pagamento</h4>
                                <p><strong>Método:</strong> <span class="metodo-pagamento"><?php echo formatarMetodoPagamento($pedido['metodo_pagamento']); ?></span></p>
                                <p><strong>Status do pagamento:</strong> <?php echo $pedido['data_pagamento'] ? 'Pago em ' . date('d/m/Y', strtotime($pedido['data_pagamento'])) : 'Aguardando pagamento'; ?></p>
                                <?php if ($pedido['metodo_pagamento'] === 'pix' && $pedido['codigo_pix']): ?>
                                    <p><strong>Código PIX:</strong> <?php echo htmlspecialchars($pedido['codigo_pix']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="info-entrega">
                                <h4>Informações de Entrega</h4>
                                <?php if ($pedido['data_entrega']): ?>
                                    <p><strong>Entregue em:</strong> <?php echo date('d/m/Y', strtotime($pedido['data_entrega'])); ?></p>
                                <?php else: ?>
                                    <p><strong>Previsão de entrega:</strong> 5-7 dias úteis</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="itens-pedido">
                            <h4>Itens do Pedido (<?php echo count($itens); ?>)</h4>
                            <div class="lista-itens">
                                <?php foreach ($itens as $item): ?>
                                    <div class="item-pedido">
                                        <div class="imagem-item">
                                            <?php if (!empty($item['imagem_url'])): ?>
                                                <img src="../<?php echo htmlspecialchars($item['imagem_url']); ?>">
                                            <?php else: ?>
                                                <div style="width:100%;height:100%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="info-item">
                                            <div class="nome-item"><?php echo htmlspecialchars($item['nome']); ?></div>
                                            <div class="artista-item">por <?php echo htmlspecialchars($item['artista']); ?></div>
                                        </div>
                                        <div class="preco-item">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="total-pedido">
                            <div class="valor-total">
                                Total: R$ <?php echo number_format($pedido['valor_total'] ?: $pedido['valor_calculado'], 2, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
    <div class="social">
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="#"><i class="fab fa-whatsapp"></i></a>
    </div>
</footer>

<script>
// Sistema de notificações do carrinho
function atualizarBadgeCarrinho() {
    const badge = document.getElementById('carrinhoBadge');
    const totalNotificacoes = <?php echo count($_SESSION['carrinho_notificacoes'] ?? []); ?>;
    if (totalNotificacoes > 0) {
        badge.textContent = totalNotificacoes;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    atualizarBadgeCarrinho();
    
    // Dropdown do perfil
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        document.addEventListener('click', function (e) {
            if (!profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
        profileDropdown.addEventListener('click', function (e) { e.stopPropagation(); });
    }
});
</script>
</body>
</html>
<?php
$conn->close();
?>