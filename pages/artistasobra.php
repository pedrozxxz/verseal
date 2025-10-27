<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;

$produtos = [
    1 => [
        "id" => 1,
        "img" => "../img/imagem2.png",
        "nome" => "Obra da Jamile",
        "artista" => "Jamile Framquilim",
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
        "nome" => "Obra da Jamile", 
        "artista" => "Jamile Framquilim",
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
        "artista" => "Jamile Framquilim",
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
        "artista" => "Jamile Framquilim", 
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
        "artista" => "Jamile Framquilim",
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
        "artista" => "Jamile Framquilim",
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
        "artista" => "Jamile Framquilim",
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
        "artista" => "Jamile Framquilim",
        "preco" => 189.99,
        "desc" => "Panorama urbano noturno",
        "dimensao" => "80 x 120cm",
        "tecnica" => "Urban sketching",
        "ano" => 2024,
        "material" => "Tinta acrílica",
        "categoria" => ["manual", "colorido"]
    ]
];

// Processar filtros se existirem
$filtroArtista = $_GET['artista'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? [];
$ordenacao = $_GET['ordenacao'] ?? 'preco_asc';

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
            if (in_array($categoria, $produto['categoria'])) {
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
    // Ordenar por ID (simulando data)
    usort($produtosFiltrados, function($a, $b) {
        return $b['id'] <=> $a['id'];
    });
}
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
    .btn-aplicar-filtros{
      background: #cc624e;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      cursor: pointer;
      margin-top: 15px;
      transition: background 0.3s;
    }
    .btn-aplicar-filtros:hover {
      background: #e07b67;
    }
    .btn-adiconar-obra{
      background: #cc624e;
      color: white;
      font-weight: bold;
      font-size: 0,6rem;
      border: none;
      padding: 13px 50px;
      border-radius: 20px;
      cursor: pointer;
      margin-top: 50px;
      transition: background 0.3s;
    }
    .btn-adiconar-obra:hover {
      background: #e07b67;
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
    button {
  font-family: 'Open Sans', sans-serif;
  font-weight: 600;
  transition: all 0.3s ease;
}

/* Botão Comprar (Card da obra) */
.btn-comprar {
  background: linear-gradient(135deg, #cc624e, #e07b67);
  border: none;
  color: white;
  padding: 12px 20px;
  border-radius: 30px;
  cursor: pointer;
  font-size: 0.95rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 10px rgba(204, 98, 78, 0.4);
}

.btn-comprar:hover {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(204, 98, 78, 0.5);
}

/* Botão Ver Detalhes */
.btn-detalhes {
  background: transparent;
  border: 2px solid #cc624e;
  color: #cc624e;
  padding: 10px 20px;
  border-radius: 30px;
  cursor: pointer;
  font-size: 0.95rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
}

.btn-detalhes:hover {
  background: #cc624e;
  color: white;
  transform: translateY(-2px);
}

/* Botão Buscar (Barra de busca) */
.busca-artista button {
  background: linear-gradient(135deg, #cc624e, #e07b67);
  color: white;
  border-radius: 12px;
  padding: 10px 18px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
}

.busca-artista button:hover {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  transform: translateY(-1px);
}

/* Botão Aplicar Filtros */
.btn-aplicar-filtros {
  background: #cc624e;
  color: white;
  border: none;
  padding: 12px 25px;
  border-radius: 25px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
}

.btn-aplicar-filtros:hover {
  background: #e07b67;
  transform: translateY(-2px);
}

/* Botão Limpar Filtros */
.btn-limpar-filtros {
  background: #6c757d;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 25px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
}

.btn-limpar-filtros:hover {
  background: #5a6268;
  transform: translateY(-1px);
}

/* Botões do Modal */
.btn-comprar-modal {
  background: linear-gradient(135deg, #cc624e, #e07b67);
  color: white;
  border-radius: 12px;
  padding: 12px 25px;
  border: none;
  font-weight: bold;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  transition: all 0.3s ease;
}

.btn-comprar-modal:hover {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  transform: translateY(-2px);
}

.btn-fechar-modal {
  background: #6c757d;
  color: white;
  border-radius: 12px;
  padding: 12px 25px;
  border: none;
  font-weight: bold;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  transition: all 0.3s ease;
}

.btn-fechar-modal:hover {
  background: #5a6268;
  transform: translateY(-2px);
}

/* Botões Ordenação */
.btn-ordenar {
  padding: 8px 18px;
  border: 2px solid #cc624e;
  border-radius: 30px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-ordenar:hover,
.btn-ordenar.ativo {
  background: #cc624e;
  color: white;
  transform: translateY(-1px);
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
    @media (max-width: 768px) {
      .modal-body {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      .modal-conteudo {
        width: 95%;
        margin: 20px;
      }
    }
  </style>
</head>

<body>
<header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="artistahome.php"><i class="fas fa-home"></i> Início</a>
      <a href="artistasobra.php"><i class="fas fa-palette"></i> Obras</a>
      <a href="artistabiografia.php"><i class="fas fa-user"></i> Quem eu sou?</a>
    
    <div class="hamburger-menu-desktop">
      <input type="checkbox" id="menu-toggle-desktop">
      <label for="menu-toggle-desktop" class="hamburger-desktop"><i class="fas fa-bars"></i><span>ACESSO</span></label>
      <div class="menu-content-desktop">
        <div class="menu-section">
          <a href="../index.php" class="menu-item"><i class="fas fa-user"></i><span>Cliente</span></a>
          <a href="./admhome.php" class="menu-item"><i class="fas fa-user-shield"></i><span>ADM</span></a>
          <a href="./artistahome.php" class="menu-item"><i class="fas fa-palette"></i><span>Artista</span></a>
        </div>
      </div>
    </div>

    <div class="profile-dropdown">
      <a href="./perfil.php" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
      <div class="dropdown-content" id="profile-dropdown">
          <div class="user-info"><p>Seja bem-vindo, <?php echo htmlspecialchars($usuarioLogado); ?>!</p></div>
          <div class="dropdown-divider"></div>
          <a href="./artistaperfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
          <div class="dropdown-divider"></div>
          <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
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
        <a href="?ordenacao=preco_asc<?php echo !empty($filtroArtista) ? '&artista=' . urlencode($filtroArtista) : ''; ?>" 
           class="btn-ordenar <?php echo $ordenacao === 'preco_asc' ? 'ativo' : ''; ?>">
          Menor Preço
        </a>
        <a href="?ordenacao=preco_desc<?php echo !empty($filtroArtista) ? '&artista=' . urlencode($filtroArtista) : ''; ?>" 
           class="btn-ordenar <?php echo $ordenacao === 'preco_desc' ? 'ativo' : ''; ?>">
          Maior Preço
        </a>
        <a href="?ordenacao=recentes<?php echo !empty($filtroArtista) ? '&artista=' . urlencode($filtroArtista) : ''; ?>" 
           class="btn-ordenar <?php echo $ordenacao === 'recentes' ? 'ativo' : ''; ?>">
          Recentes
        </a>
      </div>
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
          <?php if (!empty($filtroCategoria)): ?>
            <a href="?<?php echo !empty($filtroArtista) ? 'artista=' . urlencode($filtroArtista) : ''; ?>" 
               class="btn-limpar-categorias"></a>
          <?php endif; ?>

<button type="button" class="btn-adiconar-obra" onclick="window.location.href='adicionar-obras.php'">
    Adicionar Obra
</button>
        </form>
      </aside>

      <!-- LISTAGEM DE OBRAS -->
      <section class="lista-obras">
        <?php if (empty($produtosFiltrados)): ?>
          <div class="nenhuma-obra">
            <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
            <h3>Nenhuma obra encontrada</h3>
            <p>Tente ajustar os filtros ou buscar por outro artista.</p>
          </div>
        <?php else: ?>
          <?php foreach ($produtosFiltrados as $produto): ?>
          <div class="obra-card">
            <img src="<?php echo $produto['img']; ?>" alt="<?php echo $produto['nome']; ?>">
            <h4><?php echo $produto['nome']; ?></h4>
            <p>Por <?php echo $produto['artista']; ?></p>
            <span class="preco-obra">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
            <button class="btn-detalhes" onclick="mostrarDetalhes(<?php echo $produto['id']; ?>)">
              EDITAR OS DETALHES DA OBRA
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
    // Dados das obras (poderia vir de um banco de dados)
    const obras = <?php echo json_encode($produtos); ?>;

    // Função para mostrar detalhes da obra
    function mostrarDetalhes(obraId) {
      const obra = obras[obraId];
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
          <button class="btn-comprar-modal" onclick="adicionarAoCarrinho(${obra.id})">
            <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
          </button>
          <a href="editar_obra2.php?id=${obra.id}" class="btn-editar-modal">
            <i class="fas fa-edit"></i> Editar Obra
          </a>
        </div>
      `;

      // Mostrar modal
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    // Fechar modal ao clicar fora
    document.getElementById('modalDetalhes').addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.remove('active');
        document.body.style.overflow = 'auto';
      }
    });

    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const modal = document.getElementById('modalDetalhes');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
      }
    });

    // Função para adicionar produto ao carrinho
    function adicionarAoCarrinho(itemId) {
      const btn = event?.target.closest('button');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
      btn.disabled = true;

      const formData = new FormData();
      formData.append('acao', 'adicionar');
      formData.append('item_id', itemId);

      fetch('carrinho.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
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
      .catch(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        Swal.fire({
          icon: 'error',
          title: 'Erro de conexão',
          text: 'Não foi possível conectar ao servidor'
        });
      });
    }

    // Dropdown Perfil
    const profileIcon = document.getElementById("profile-icon");
    const profileDropdown = document.getElementById("profile-dropdown");
    if (profileIcon && profileDropdown) {
      profileIcon.addEventListener("click", (e) => {
        e.preventDefault();
        profileDropdown.style.display =
          profileDropdown.style.display === "block" ? "none" : "block";
      });
      document.addEventListener("click", (e) => {
        if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
          profileDropdown.style.display = "none";
        }
      });
    }
</script>
</body>

</html>
