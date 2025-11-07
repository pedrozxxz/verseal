<?php
session_start();

// Verificar se usuário está logado de forma compatível
$usuarioLogado = null;
$tipoUsuario = null;

if (isset($_SESSION["usuario"])) {
    $usuarioLogado = $_SESSION["usuario"];
    $tipoUsuario = "usuario";
} elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artistas";
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

// Buscar TODOS os produtos (obras) do banco de dados
// CORREÇÃO: Buscar diretamente da tabela produtos sem JOIN
$sql_produtos = "
    SELECT p.* 
    FROM produtos p 
    WHERE p.ativo = 1
    ORDER BY p.data_cadastro DESC
";

$result_produtos = $conn->query($sql_produtos);
$produtos = [];

if ($result_produtos && $result_produtos->num_rows > 0) {
    while ($produto = $result_produtos->fetch_assoc()) {
        // Processar categorias do campo JSON
        $categorias = [];
        if (!empty($produto['categorias'])) {
            $categorias_array = json_decode($produto['categorias'], true);
            if (is_array($categorias_array)) {
                $categorias = $categorias_array;
            } else {
                // Fallback: tentar separar por vírgula se não for JSON válido
                $categorias = array_map('trim', explode(',', $produto['categorias']));
            }
        }
        
        // Criar array do produto
        $produtos[] = [
            "id" => intval($produto['id']),
            "img" => !empty($produto['imagem_url']) ? $produto['imagem_url'] : '../img/imagem2.png',
            "nome" => $produto['nome'] ?? 'Obra sem nome',
            "artista" => $produto['artista'] ?? 'Artista desconhecido',
            "preco" => floatval($produto['preco'] ?? 0),
            "descricao" => $produto['descricao'] ?? '',
            "dimensoes" => $produto['dimensoes'] ?? '',
            "tecnica" => $produto['tecnica'] ?? '',
            "ano" => intval($produto['ano'] ?? 2024),
            "material" => $produto['material'] ?? '',
            "categorias" => $categorias,
            "disponivel" => boolval($produto['ativo'] ?? true),
            "estoque" => intval($produto['estoque'] ?? 0),
            "data_cadastro" => $produto['data_cadastro'] ?? ''
        ];
    }
} else {
    // Se não encontrar produtos no banco, usar array vazio
    $produtos = [];
}

// Processar filtros se existirem
$filtroArtista = $_GET['artista'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? [];
if (!is_array($filtroCategoria)) {
    $filtroCategoria = [];
}
$ordenacao = $_GET['ordenacao'] ?? 'recentes';

// Se vier por POST (do formulário de busca)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_artista'])) {
    $filtroArtista = $_POST['artista'] ?? '';
    header('Location: ?artista=' . urlencode($filtroArtista));
    exit;
}

// Filtrar produtos
$produtosFiltrados = $produtos;

// Filtro por artista
if (!empty($filtroArtista)) {
    $produtosFiltrados = array_filter($produtosFiltrados, function($produto) use ($filtroArtista) {
        return stripos($produto['artista'], $filtroArtista) !== false;
    });
}

// Filtro por categoria
if (!empty($filtroCategoria) && is_array($filtroCategoria)) {
    $produtosFiltrados = array_filter($produtosFiltrados, function($produto) use ($filtroCategoria) {
        foreach ($filtroCategoria as $categoria) {
            if (in_array($categoria, $produto['categorias'])) {
                return true;
            }
        }
        return false;
    });
}

// Ordenação
if ($ordenacao === 'preco_asc') {
    usort($produtosFiltrados, function($a, $b) {
        return $a['preco'] <=> $b['preco'];
    });
} elseif ($ordenacao === 'preco_desc') {
    usort($produtosFiltrados, function($a, $b) {
        return $b['preco'] <=> $a['preco'];
    });
} elseif ($ordenacao === 'recentes') {
    // Ordenar por data de cadastro
    usort($produtosFiltrados, function($a, $b) {
        if (!empty($a['data_cadastro']) && !empty($b['data_cadastro'])) {
            return strtotime($b['data_cadastro']) <=> strtotime($a['data_cadastro']);
        }
        return $b['id'] <=> $a['id'];
    });
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verseal - Obras de Arte</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/produto.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* Modal Detalhes da Obra */
    .modal-detalhes {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-detalhes.active {
      display: flex;
    }

    .modal-conteudo {
      background: white;
      border-radius: 15px;
      max-width: 800px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
      animation: modalAppear 0.3s ease;
    }

    @keyframes modalAppear {
      from {
        opacity: 0;
        transform: scale(0.8);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 25px;
      border-bottom: 1px solid #eee;
    }

    .modal-header h2 {
      font-family: 'Playfair Display', serif;
      color: #cc624e;
      margin: 0;
      font-size: 1.8rem;
    }

    .btn-fechar {
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #666;
      cursor: pointer;
      padding: 5px;
      transition: color 0.3s;
    }

    .btn-fechar:hover {
      color: #cc624e;
    }

    .modal-body {
      padding: 25px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }

    .modal-imagem {
      text-align: center;
    }

    .modal-imagem img {
      max-width: 100%;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .modal-info {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .info-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .info-item:last-child {
      border-bottom: none;
    }

    .info-label {
      font-weight: 600;
      color: #333;
    }

    .info-value {
      color: #666;
      text-align: right;
    }

    .preco-destaque {
      font-size: 1.5rem;
      font-weight: bold;
      color: #cc624e;
    }

    .descricao-completa {
      grid-column: 1 / -1;
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-top: 10px;
    }

    .modal-actions {
      grid-column: 1 / -1;
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .btn-comprar-modal {
      background: #cc624e;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      flex: 1;
      transition: background 0.3s;
    }

    .btn-comprar-modal:hover {
      background: #e07b67;
    }

    .btn-fechar-modal {
      background: #6c757d;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      flex: 1;
      transition: background 0.3s;
    }

    .btn-fechar-modal:hover {
      background: #5a6268;
    }

    /* Estilos para filtros ativos */
    .filtro-ativo {
      background: #cc624e !important;
      color: white !important;
    }

    .resultados-busca {
      margin-bottom: 20px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
      border-left: 4px solid #cc624e;
    }

    .btn-limpar-filtros {
      background: #6c757d;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      margin-left: 10px;
    }

    .btn-limpar-filtros:hover {
      background: #5a6268;
    }

    .nenhuma-obra {
      text-align: center;
      padding: 60px 40px;
      background: #f8f9fa;
      border-radius: 15px;
      border: 2px dashed #dee2e6;
      margin: 20px 0;
      grid-column: 1 / -1;
    }

    .nenhuma-obra i.fa-search {
      font-size: 4rem;
      color: #ced4da;
      margin-bottom: 20px;
    }

    .nenhuma-obra h3 {
      color: #6c757d;
      margin-bottom: 15px;
      font-size: 1.5rem;
    }

    .nenhuma-obra p {
      color: #868e96;
      margin-bottom: 30px;
      font-size: 1.1rem;
      line-height: 1.6;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }

    @media (max-width: 768px) {
      .modal-body {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .modal-conteudo {
        width: 95%;
        margin: 20px;
      }
      
      .nenhuma-obra {
        padding: 40px 20px;
        margin: 15px 0;
      }

      .nenhuma-obra i.fa-search {
        font-size: 3rem;
      }

      .nenhuma-obra h3 {
        font-size: 1.3rem;
      }

      .nenhuma-obra p {
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
<!-- HEADER -->
<!-- HEADER -->
<header>
  <div class="logo">Verseal</div>
  <nav>
    <a href="../index.php">Início</a>
    <a href="../pages/produto.php">Obras</a>
    <a href="../pages/sobre.php">Sobre</a>
    <a href="../pages/artistas.php">Artistas</a>
    <a href="../pages/contato.php">Contato</a>
    
    <a href="../pages/carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
    
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
        <a href="../pages/perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
        <a href="../pages/favoritos.php" class="dropdown-item"><i class="fas fa-heart"></i> Favoritos</a>
      <?php endif; ?>

      <div class="dropdown-divider"></div>
      <a href="../pages/logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>

    <?php else: ?>
      <div class="user-info"><p>Faça login para acessar seu perfil</p></div>
      <div class="dropdown-divider"></div>
      <a href="../pages/login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
      <a href="../pages/login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
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
          <a href="./pages/admhome.php" class="menu-item"><i class="fas fa-user-shield"></i> <span>ADM</span></a>
          <a href="./pages/artistahome.php" class="menu-item"><i class="fas fa-palette"></i> <span>Artista</span></a>
        </div>
      </div>
    </div>
  </nav>
</header>

  <!-- CONTEÚDO -->
  <main class="pagina-obras">
    <h1 class="titulo-pagina">Obras de Arte</h1>

    <!-- Resultados da busca -->
    <?php if (!empty($filtroArtista) || !empty($filtroCategoria)): ?>
    <div class="resultados-busca">
      <strong>Filtros ativos:</strong>
      <?php if (!empty($filtroArtista)): ?>
        <span class="badge">Artista: <?php echo htmlspecialchars($filtroArtista); ?></span>
      <?php endif; ?>
      <?php if (!empty($filtroCategoria)): ?>
        <span class="badge">Categorias: <?php echo implode(', ', $filtroCategoria); ?></span>
      <?php endif; ?>
      <a href="?" class="btn-limpar-filtros">Limpar Filtros</a>
      <span style="margin-left: 15px; color: #666;">
        <?php echo count($produtosFiltrados); ?> obra(s) encontrada(s)
      </span>
    </div>
    <?php endif; ?>

    <!-- Barra de Ordenação e Busca -->
    <div class="barra-filtros-topo">
      <div class="ordenacao">
        <span>Ordenação:</span>
        <a href="?ordenacao=preco_asc<?php echo !empty($filtroArtista) ? '&artista=' . urlencode($filtroArtista) : ''; echo !empty($filtroCategoria) ? '&' . http_build_query(['categoria' => $filtroCategoria]) : ''; ?>" 
           class="btn-ordenar <?php echo $ordenacao === 'preco_asc' ? 'ativo' : ''; ?>">
          Menor Preço
        </a>
        <a href="?ordenacao=preco_desc<?php echo !empty($filtroArtista) ? '&artista=' . urlencode($filtroArtista) : ''; echo !empty($filtroCategoria) ? '&' . http_build_query(['categoria' => $filtroCategoria]) : ''; ?>" 
           class="btn-ordenar <?php echo $ordenacao === 'preco_desc' ? 'ativo' : ''; ?>">
          Maior Preço
        </a>
        <a href="?ordenacao=recentes<?php echo !empty($filtroArtista) ? '&artista=' . urlencode($filtroArtista) : ''; echo !empty($filtroCategoria) ? '&' . http_build_query(['categoria' => $filtroCategoria]) : ''; ?>" 
           class="btn-ordenar <?php echo $ordenacao === 'recentes' ? 'ativo' : ''; ?>">
          Recentes
        </a>
      </div>
      <form method="POST" class="busca-artista">
        <input type="text" name="artista" placeholder="Procurar por artista..." 
               value="<?php echo htmlspecialchars($filtroArtista); ?>">
        <button type="submit" name="buscar_artista">Buscar</button>
      </form>
    </div>

    <div class="conteudo-obras">
      <!-- FILTRO LATERAL -->
      <aside class="filtro">
        <h3>Filtro</h3>

        <form method="GET">
          <!-- Manter o filtro de artista se existir -->
          <?php if (!empty($filtroArtista)): ?>
            <input type="hidden" name="artista" value="<?php echo htmlspecialchars($filtroArtista); ?>">
          <?php endif; ?>

          <div class="filtro-box">
            <p>Categoria</p>
            <label>
              <input type="checkbox" name="categoria[]" value="manual" 
                     <?php echo in_array('manual', $filtroCategoria) ? 'checked' : ''; ?>> 
              Manual
            </label>
            <label>
              <input type="checkbox" name="categoria[]" value="digital"
                     <?php echo in_array('digital', $filtroCategoria) ? 'checked' : ''; ?>> 
              Digital
            </label>
            <label>
              <input type="checkbox" name="categoria[]" value="preto e branco"
                     <?php echo in_array('preto e branco', $filtroCategoria) ? 'checked' : ''; ?>> 
              Preto e Branco
            </label>
            <label>
              <input type="checkbox" name="categoria[]" value="colorido"
                     <?php echo in_array('colorido', $filtroCategoria) ? 'checked' : ''; ?>> 
              Colorido
            </label>
          </div>

          <button type="submit" class="btn-aplicar-filtros">Aplicar Filtros</button>
        </form>
      </aside>

      <!-- LISTAGEM DE OBRAS -->
      <section class="lista-obras">
        <?php if (empty($produtosFiltrados)): ?>
          <div class="nenhuma-obra">
            <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
            <h3>Nenhuma obra encontrada</h3>
            <p>Tente ajustar os filtros ou buscar por outro artista.</p>
            <a href="?" class="btn-limpar-filtros">Limpar Todos os Filtros</a>
          </div>
        <?php else: ?>
          <?php foreach ($produtosFiltrados as $produto): ?>
          <div class="obra-card">
            <img src="<?php echo $produto['img']; ?>" alt="<?php echo $produto['nome']; ?>">
            <h4><?php echo $produto['nome']; ?></h4>
            <p>Por <?php echo $produto['artista']; ?></p>
            <span class="preco-obra">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
            <button class="btn-comprar" onclick="adicionarAoCarrinho(<?php echo $produto['id']; ?>)">
              <i class="fas fa-shopping-cart"></i> Comprar
            </button>
            <button class="btn-detalhes" onclick="mostrarDetalhes(<?php echo $produto['id']; ?>)">
              Ver Detalhes da Obra
            </button>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <!-- MODAL DETALHES DA OBRA -->
  <div id="modalDetalhes" class="modal-detalhes">
    <div class="modal-conteudo">
      <div class="modal-header">
        <h2 id="modalTitulo">Detalhes da Obra</h2>
        <button class="btn-fechar" onclick="fecharModal()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Conteúdo será preenchido via JavaScript -->
      </div>
    </div>
  </div>

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
    // Dados das obras
    const obras = <?php echo json_encode($produtos); ?>;

    // Função para mostrar detalhes da obra
    function mostrarDetalhes(obraId) {
      const obra = obras.find(o => o.id === obraId);
      if (!obra) return;

      const modal = document.getElementById('modalDetalhes');
      const modalTitulo = document.getElementById('modalTitulo');
      const modalBody = document.getElementById('modalBody');

      // Preencher título
      modalTitulo.textContent = obra.nome;

      // Preencher conteúdo
      modalBody.innerHTML = `
        <div class="modal-imagem">
          <img src="${obra.img}" alt="${obra.nome}">
        </div>
        <div class="modal-info">
          <div class="info-item">
            <span class="info-label">Artista:</span>
            <span class="info-value">${obra.artista}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Preço:</span>
            <span class="info-value preco-destaque">R$ ${obra.preco.toFixed(2).replace('.', ',')}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Dimensões:</span>
            <span class="info-value">${obra.dimensao}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Técnica:</span>
            <span class="info-value">${obra.tecnica}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Ano:</span>
            <span class="info-value">${obra.ano}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Material:</span>
            <span class="info-value">${obra.material}</span>
          </div>
        </div>
        <div class="descricao-completa">
          <h4>Descrição da Obra</h4>
          <p>${obra.desc}</p>
        </div>
        <div class="modal-actions">
          <button class="btn-comprar-modal" onclick="adicionarAoCarrinho(${obra.id}); fecharModal()">
            <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
          </button>
          <button class="btn-fechar-modal" onclick="fecharModal()">
            <i class="fas fa-times"></i> Fechar
          </button>
        </div>
      `;

      // Mostrar modal
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    // Função para fechar modal
    function fecharModal() {
      const modal = document.getElementById('modalDetalhes');
      modal.classList.remove('active');
      document.body.style.overflow = 'auto';
    }

    // Fechar modal ao clicar fora
    document.getElementById('modalDetalhes').addEventListener('click', function(e) {
      if (e.target === this) {
        fecharModal();
      }
    });

    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        fecharModal();
      }
    });

    // Função para adicionar produto ao carrinho
    function adicionarAoCarrinho(itemId) {
      // Mostrar loading
      const btn = event?.target || document.querySelector(`.btn-comprar-modal`);
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
      btn.disabled = true;

      const formData = new FormData();
      formData.append('acao', 'adicionar');
      formData.append('item_id', itemId);

      fetch('carrinho.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Erro na rede');
        }
        return response.json();
      })
      .then(data => {
        // Restaurar botão
        btn.innerHTML = originalText;
        btn.disabled = false;

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Obra adicionada!',
            text: data.message,
            timer: 2000,
            showConfirmButton: false,
            position: 'top-end'
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: data.message || 'Erro ao adicionar obra ao carrinho'
          });
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        // Restaurar botão
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        Swal.fire({
          icon: 'error',
          title: 'Erro de conexão',
          text: 'Não foi possível conectar ao servidor'
        });
      });
    }
 // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');

      profileIcon.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
      });

      // Fechar dropdown ao clicar fora
      document.addEventListener('click', function (e) {
        if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
          profileDropdown.style.display = 'none';
        }
      });

      // Prevenir fechamento ao clicar dentro do dropdown
      profileDropdown.addEventListener('click', function (e) {
        e.stopPropagation();
      });

      // Menu Hamburguer Desktop
      const menuToggleDesktop = document.getElementById('menu-toggle-desktop');
      const menuContentDesktop = document.querySelector('.menu-content-desktop');

      // Fechar menu ao clicar fora
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.hamburger-menu-desktop')) {
          menuToggleDesktop.checked = false;
        }
      });

      // Fechar menu ao clicar em um item (exceto Cliente)
      const menuItems = document.querySelectorAll('.menu-item');
      menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
          // Não fecha o menu se for o item Cliente
          if (!this.querySelector('i').classList.contains('fa-user')) {
            menuToggleDesktop.checked = false;
          }
        });
      });
    });
  </script>

</body>
</html>