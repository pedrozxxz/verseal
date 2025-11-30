<?php
session_start();

require_once 'config.php';

// DEBUG: Verificar a sessão
error_log("Sessão usuario: " . print_r($_SESSION["usuario"] ?? 'Não definido', true));
error_log("Tipo: " . gettype($_SESSION["usuario"] ?? 'null'));
// Verificar se o usuário está logado
if (!isset($_SESSION["usuario"]) || !is_array($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$usuarioLogado = $_SESSION["usuario"];

// Verificar se temos o nome do usuário
if (!isset($usuarioLogado['nome']) || empty($usuarioLogado['nome'])) {
    die("Erro: Nome do usuário não encontrado na sessão.");
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

$sql_obras = "
    SELECT p.*,
           CASE 
             WHEN pi.produto_id IS NOT NULL THEN 'vendida'
             ELSE 'disponivel'
           END AS status
    FROM produtos p
    LEFT JOIN itens_pedido pi ON pi.produto_id = p.id
    WHERE p.artista = ?
    ORDER BY p.data_cadastro DESC
";

$stmt_obras = $conn->prepare($sql_obras);
$nomeArtista = $usuarioLogado['nome'];
$stmt_obras->bind_param("s", $nomeArtista);
$stmt_obras->execute();
$result_obras = $stmt_obras->get_result();
$produtos = [];

if ($result_obras) {
    while ($obra = $result_obras->fetch_assoc()) {
        // 🔹 CORREÇÃO: TRATAMENTO DO CAMINHO DA IMAGEM (igual ao produto.php)
        $imagem_url = '';
        if (!empty($obra['imagem_url'])) {
            if (strpos($obra['imagem_url'], '../') === 0) {
                $imagem_url = $obra['imagem_url'];
            } elseif (strpos($obra['imagem_url'], 'img/') === 0) {
                $imagem_url = '../' . $obra['imagem_url'];
            } elseif (strpos($obra['imagem_url'], 'uploads/') === 0) {
                $imagem_url = '../' . $obra['imagem_url'];
            } elseif (strpos($obra['imagem_url'], 'img/uploads/') === 0) {
                $imagem_url = '../' . $obra['imagem_url'];
            } else {
                $imagem_url = $obra['imagem_url'];
            }
        } else {
            $imagem_url = '../img/imagem2.png'; // imagem padrão
        }

        // Processar categorias
        $categorias = [];
        if (!empty($obra['categorias'])) {
            $categorias_array = json_decode($obra['categorias'], true);
            if (is_array($categorias_array)) {
                $categorias = $categorias_array;
            } else {
                $categorias = array_map('trim', explode(',', $obra['categorias']));
            }
        }

        $produtos[] = [ // 🔹 CORREÇÃO: Mudar para array numérico em vez de associativo por ID
            "id" => intval($obra['id']),
            "img" => $imagem_url,
            "nome" => $obra['nome'] ?? '',
            "artista" => $obra['artista'] ?? '',
            "preco" => floatval($obra['preco'] ?? 0),
            "desc" => $obra['descricao'] ?? '',
            "dimensao" => $obra['dimensoes'] ?? '',
            "tecnica" => $obra['tecnica'] ?? '',
            "ano" => intval($obra['ano'] ?? 0),
            "material" => $obra['material'] ?? '',
            "categoria" => $categorias,
            "data_cadastro" => $obra['data_cadastro'] ?? '',
            "data_criacao" => $obra['data_cadastro'] ?? '',
            "status" => $obra['status'] ?? 'disponivel'

        ];
    }
}

// Processar filtros
$filtroCategoria = $_GET['categoria'] ?? [];
if (!is_array($filtroCategoria)) {
    $filtroCategoria = [];
}
$ordenacao = $_GET['ordenacao'] ?? 'recentes';
$obraEditada = $_GET['obra_editada'] ?? null;
$obraNova = $_GET['obra_nova'] ?? null;

// Garantir que $produtos seja um array
if (!is_array($produtos)) {
    $produtos = [];
}

// 🔹 CORREÇÃO: Filtrar produtos
$produtosFiltrados = $produtos;

// Filtro por categoria - CORREÇÃO
if (!empty($filtroCategoria) && is_array($filtroCategoria)) {
    $produtosFiltrados = array_filter($produtosFiltrados, function($produto) use ($filtroCategoria) {
        if (!isset($produto['categoria']) || !is_array($produto['categoria'])) {
            return false;
        }
        
        // Verificar se alguma categoria do filtro está presente na obra
        foreach ($filtroCategoria as $categoriaFiltro) {
            if (in_array($categoriaFiltro, $produto['categoria'])) {
                return true;
            }
        }
        return false;
    });
}

// 🔹 CORREÇÃO: Ordenação melhorada
if ($ordenacao === 'preco_asc') {
    usort($produtosFiltrados, function($a, $b) {
        return ($a['preco'] ?? 0) <=> ($b['preco'] ?? 0);
    });
} elseif ($ordenacao === 'preco_desc') {
    usort($produtosFiltrados, function($a, $b) {
        return ($b['preco'] ?? 0) <=> ($a['preco'] ?? 0);
    });
} elseif ($ordenacao === 'recentes') {
    usort($produtosFiltrados, function($a, $b) {
        // 🔹 CORREÇÃO: Usar data_cadastro para ordenação
        $timeA = isset($a['data_cadastro']) ? strtotime($a['data_cadastro']) : 0;
        $timeB = isset($b['data_cadastro']) ? strtotime($b['data_cadastro']) : 0;
        return $timeB <=> $timeA;
    });
}

// 🔹 CORREÇÃO: Destacar obra nova ou editada
if ($obraNova) {
    $obraEditada = $obraNova;
}

// Garantir que produtosFiltrados seja array
if (!is_array($produtosFiltrados)) {
    $produtosFiltrados = [];
}

// Configurações para o header
$tipoUsuario = 'artista';
// 🔹 SISTEMA DE NOTIFICAÇÕES - Buscar total de mensagens não lidas


// 🔹 Verificar mensagens não lidas
$total_nao_lidas = 0;
if (isset($usuarioLogado['id']) && function_exists('getTotalMensagensNaoLidas')) {
    $total_nao_lidas = getTotalMensagensNaoLidas($conn, $usuarioLogado['id']);
}

// Verificar se temos o nome do usuário
if (!isset($usuarioLogado['nome']) || empty($usuarioLogado['nome'])) {
    die("Erro: Nome do usuário não encontrado na sessão.");
}
// 🔹 CORREÇÃO: Fechar conexão
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verseal - Minhas Obras</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/produto.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* Seus estilos CSS existentes aqui */
    .modal-detalhes { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 1000; justify-content: center; align-items: center; }
    .modal-detalhes.active { display: flex; }
    .modal-conteudo { background: white; border-radius: 15px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; animation: modalAppear 0.3s ease; }
    @keyframes modalAppear { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid #eee; }
    .modal-header h2 { font-family: 'Playfair Display', serif; color: #cc624e; margin: 0; font-size: 1.8rem; }
    .btn-fechar { background: none; border: none; font-size: 1.5rem; color: #666; cursor: pointer; padding: 5px; transition: color 0.3s; }
    .btn-fechar:hover { color: #cc624e; }
    .btn-aplicar-filtros, .btn-adiconar-obra { background: #cc624e; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; margin-top: 15px; transition: background 0.3s; }
    .btn-aplicar-filtros:hover, .btn-adiconar-obra:hover { background: #e07b67; }
    .btn-adiconar-obra { font-weight: bold; font-size: 0.9rem; padding: 13px 25px; margin-top: 0; }
    .modal-body { padding: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .modal-imagem { text-align: center; }
    .modal-imagem img { max-width: 100%; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
    .modal-info { display: flex; flex-direction: column; gap: 15px; }
    .info-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
    .info-item:last-child { border-bottom: none; }
    .info-label { font-weight: 600; color: #333; }
    .info-value { color: #666; text-align: right; }
    .preco-destaque { font-size: 1.5rem; font-weight: bold; color: #cc624e; }
    .descricao-completa { grid-column: 1 / -1; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 10px; }
    .modal-actions { grid-column: 1 / -1; display: flex; gap: 15px; margin-top: 20px; }
    
    .nenhuma-obra {
        text-align: center;
        padding: 60px 40px;
        background: #f8f9fa;
        border-radius: 15px;
        border: 2px dashed #dee2e6;
        margin: 20px 0;
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

    .btn-adicionar-primeira {
        background: linear-gradient(135deg, #cc624e, #e07b67);
        color: white;
        border: none;
        padding: 15px 35px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: bold;
        font-size: 1rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(204, 98, 78, 0.3);
        margin-top: 10px;
    }

    .btn-adicionar-primeira:hover {
        background: linear-gradient(135deg, #e07b67, #cc624e);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(204, 98, 78, 0.4);
        text-decoration: none;
        color: white;
    }

    .btn-adicionar-primeira i {
        font-size: 1.1rem;
    }

    /* Efeito de animação suave */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .btn-adicionar-primeira {
        animation: pulse 2s infinite;
    }

    .btn-adicionar-primeira:hover {
        animation: none;
    }

    /* Para telas menores */
    @media (max-width: 768px) {
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

        .btn-adicionar-primeira {
            padding: 12px 25px;
            font-size: 0.95rem;
        }
    }
    
    .info-usuario {
        background: #e7f3ff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        border-left: 4px solid #007bff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .info-usuario-content {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }
    
    .info-usuario i {
        color: #e4e2e2ff;
        font-size: 1.2rem;
    }
    
    .info-usuario strong {
        color: #0056b3;
        font-size: 1rem;
    }
    
    .info-usuario span {
        color: #666;
        font-size: 0.95rem;
    }
    
    .btn-adiconar-obra {
        background: #cc624e;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: bold;
        font-size: 0.9rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    
    .btn-adiconar-obra:hover {
        background: #e07b67;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(204, 98, 78, 0.3);
        text-decoration: none;
        color: white;
    }
    
    /* Para telas menores */
    @media (max-width: 768px) {
        .info-usuario {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .info-usuario-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .btn-adiconar-obra {
            align-self: stretch;
            text-align: center;
            justify-content: center;
        }
    }
    
    .obra-destaque { 
        border: 2px solid #cc624e; 
        box-shadow: 0 4px 15px rgba(204, 98, 78, 0.3); 
        transform: scale(1.02); 
        transition: all 0.3s ease; 
        position: relative;
    }
    
    .badge-editado { 
        background: #28a745; 
        color: white; 
        padding: 4px 8px; 
        border-radius: 12px; 
        font-size: 0.8rem; 
        margin-left: 10px; 
    }
    
    .badge-nova {
        background: #17a2b8;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-left: 10px;
    }
    
    .mensagem-sucesso { 
        background: #d4edda; 
        color: #155724; 
        padding: 15px; 
        border-radius: 5px; 
        margin-bottom: 20px; 
        border: 1px solid #c3e6cb; 
    }
    
    /* 🔹 CORREÇÃO: ESTILOS PARA IMAGENS */
    .obra-card img {
      object-fit: cover;
      height: 250px;
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
    
    /* 🔹 NOVO: Estilo para indicadores de filtro ativo */
    .filtros-ativos {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #cc624e;
    }
    
    .filtro-ativo {
        display: inline-block;
        background: #cc624e;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        margin-right: 8px;
        margin-bottom: 5px;
    }
    
    .btn-limpar-filtros {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.8rem;
        margin-top: 10px;
        display: inline-block;
    }
    
    .btn-limpar-filtros:hover {
        background: #5a6268;
    }
    
    @media (max-width: 768px) {
      .modal-body { grid-template-columns: 1fr; gap: 20px; }
      .modal-conteudo { width: 95%; margin: 20px; }
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

      <?php if (!empty($usuarioLogado['id'])): ?>
      <div class="notificacao-mensagens">
          <a href="artistaperfil.php?aba=mensagens" class="icon-link">
              <i class="fas fa-envelope"></i>
              
              <?php if ($total_nao_lidas > 0): ?>
                <span class="mensagens-badge" id="mensagensBadge"><?php echo $total_nao_lidas; ?></span>
              <?php endif; ?>
          </a>
      </div>
      <?php endif; ?>

      <!-- DROPDOWN PERFIL -->
      <div class="profile-dropdown">
          <a href="#" class="icon-link" id="profile-icon">
            <i class="fas fa-user"></i>
          </a>

          <div class="dropdown-content" id="profile-dropdown">
            <?php if (!empty($usuarioLogado['nome'])): ?>
              <div class="user-info">
                <p>Bem-vindo, <span id="user-name"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>!</p>
                <small><?php echo ucfirst($tipoUsuario); ?></small>
              </div>

              <div class="dropdown-divider"></div>
              <a href="./artistaperfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
              <a href="./editarbiografia.php" class="dropdown-item"><i class="fas fa-edit"></i> Editar Biografia</a>

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

  <!-- CONTEÚDO -->
  <main class="pagina-obras">
    <h1 class="titulo-pagina">Minhas Obras de Arte</h1>

    <!-- Informação do usuário -->
    <div class="info-usuario">
    <div class="info-usuario-content">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>Visualizando apenas suas obras</strong>
            <span style="display: block; margin-top: 5px;">
                Você está vendo <?php echo count($produtos); ?> obra(s) cadastrada(s) em sua conta.
                <?php if (!empty($filtroCategoria)): ?>
                <br><small><strong>Filtro ativo:</strong> <?php echo count($produtosFiltrados); ?> obra(s) correspondem</small>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <a href="../pages/adicionarobra.php" class="btn-adiconar-obra">
        <i class="fas fa-plus"></i> Adicionar Nova Obra
    </a>
</div>

    <!-- 🔹 CORREÇÃO: Mostrar filtros ativos -->
    <?php if (!empty($filtroCategoria) || $obraEditada || $obraNova): ?>
    <div class="filtros-ativos">
        <strong>Filtros ativos:</strong>
        <?php if (!empty($filtroCategoria)): ?>
            <?php foreach ($filtroCategoria as $categoria): ?>
                <span class="filtro-ativo"><?php echo htmlspecialchars($categoria); ?></span>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($obraEditada): ?>
            <span class="filtro-ativo">Obra em destaque</span>
        <?php endif; ?>
        
        <?php if ($ordenacao !== 'recentes'): ?>
            <span class="filtro-ativo">Ordenação: <?php echo $ordenacao === 'preco_asc' ? 'Menor Preço' : 'Maior Preço'; ?></span>
        <?php endif; ?>
        
        <a href="?" class="btn-limpar-filtros">
            <i class="fas fa-times"></i> Limpar Todos os Filtros
        </a>
    </div>
    <?php endif; ?>

    <!-- Mensagem de obra editada -->
    <?php if ($obraEditada): ?>
    <div class="mensagem-sucesso">
      <i class="fas fa-check-circle"></i> 
      <strong>
          <?php echo $obraNova ? 'Obra adicionada com sucesso!' : 'Obra atualizada com sucesso!'; ?>
      </strong>
      <span style="margin-left: 15px;">
        <?php 
        $obraDestaque = null;
        foreach ($produtos as $prod) {
            if ($prod['id'] == $obraEditada) {
                $obraDestaque = $prod;
                break;
            }
        }
        if ($obraDestaque): ?>
            A obra "<?php echo htmlspecialchars($obraDestaque['nome']); ?>" foi 
            <?php echo $obraNova ? 'adicionada' : 'editada'; ?> e aparece em destaque.
        <?php endif; ?>
      </span>
    </div>
    <?php endif; ?>

    <!-- Barra de Ordenação -->
    <div class="barra-filtros-topo">
      <div class="ordenacao">
        <span>Ordenação:</span>
        <a href="?ordenacao=preco_asc<?php 
          echo !empty($filtroCategoria) ? '&' . http_build_query(['categoria' => $filtroCategoria]) : '';
          echo $obraEditada ? '&obra_editada=' . $obraEditada : '';
        ?>" class="btn-ordenar <?php echo $ordenacao === 'preco_asc' ? 'ativo' : ''; ?>">
          Menor Preço
        </a>
        <a href="?ordenacao=preco_desc<?php 
          echo !empty($filtroCategoria) ? '&' . http_build_query(['categoria' => $filtroCategoria]) : '';
          echo $obraEditada ? '&obra_editada=' . $obraEditada : '';
        ?>" class="btn-ordenar <?php echo $ordenacao === 'preco_desc' ? 'ativo' : ''; ?>">
          Maior Preço
        </a>
        <a href="?ordenacao=recentes<?php 
          echo !empty($filtroCategoria) ? '&' . http_build_query(['categoria' => $filtroCategoria]) : '';
          echo $obraEditada ? '&obra_editada=' . $obraEditada : '';
        ?>" class="btn-ordenar <?php echo $ordenacao === 'recentes' ? 'ativo' : ''; ?>">
          Recentes
        </a>
      </div>
    </div>

    <div class="conteudo-obras">
      <!-- FILTRO LATERAL -->
      <aside class="filtro">
        <h3>Filtro</h3>

        <form method="GET">
          <!-- Manter parâmetros importantes -->
          <?php if ($obraEditada): ?>
            <input type="hidden" name="obra_editada" value="<?php echo $obraEditada; ?>">
          <?php endif; ?>
          
          <?php if ($ordenacao !== 'recentes'): ?>
            <input type="hidden" name="ordenacao" value="<?php echo $ordenacao; ?>">
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

          <button type="submit" class="btn-aplicar-filtros">
            <i class="fas fa-filter"></i> Aplicar Filtros
          </button>
          
          <?php if (!empty($filtroCategoria) || $obraEditada || $ordenacao !== 'recentes'): ?>
            <a href="?" class="btn-limpar-filtros" style="margin-top: 10px; display: inline-block;">
              <i class="fas fa-times"></i> Limpar Filtros
            </a>
          <?php endif; ?>
        </form>
      </aside>

      <!-- LISTAGEM DE OBRAS -->
      <section class="lista-obras">
        <?php if (empty($produtosFiltrados)): ?>
          <div class="nenhuma-obra">
            <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
            <h3>Nenhuma obra encontrada</h3>
            <p>
                <?php if (!empty($filtroCategoria)): ?>
                    Nenhuma obra corresponde aos filtros aplicados. Tente ajustar os filtros.
                <?php else: ?>
                    Você ainda não possui obras cadastradas.
                <?php endif; ?>
            </p>
            <a href="adicionarobra.php" class="btn-adicionar-primeira">              
              <i class="fas fa-plus"></i> Adicionar Primeira Obra
            </a>
          </div>
        <?php else: ?>
          <?php foreach ($produtosFiltrados as $produto): ?>
          <div class="obra-card <?php echo $obraEditada == $produto['id'] ? 'obra-destaque' : ''; ?>" 
               id="obra-<?php echo $produto['id']; ?>">
            <!-- 🔹 CORREÇÃO: IMAGEM COM TRATAMENTO DE ERRO -->
            <img src="<?php echo $produto['img']; ?>" 
                 alt="<?php echo $produto['nome']; ?>"
                 onerror="this.onerror=null; this.src='../img/imagem2.png'; this.classList.add('img-error');">
            <h4>
              <?php echo $produto['nome']; ?>
              <?php if ($obraEditada == $produto['id']): ?>
                <span class="<?php echo $obraNova ? 'badge-nova' : 'badge-editado'; ?>">
                    <?php echo $obraNova ? 'NOVA' : 'EDITADA'; ?>
                </span>
              <?php endif; ?>
            </h4>
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
    // 🔹 CORREÇÃO: Dados das obras - converter para objeto por ID para fácil acesso
    const obrasArray = <?php echo json_encode($produtos); ?>;
    const obras = {};
    obrasArray.forEach(obra => {
        obras[obra.id] = obra;
    });

    // Função para mostrar detalhes da obra
    function mostrarDetalhes(obraId) {
      const obra = obras[obraId];
      if (!obra) {
          console.error('Obra não encontrada:', obraId);
          return;
      }

      const modal = document.getElementById('modalDetalhes');
      const modalTitulo = document.getElementById('modalTitulo');
      const modalBody = document.getElementById('modalBody');

      // Preencher título
      modalTitulo.textContent = obra.nome;

      // Preencher conteúdo
      modalBody.innerHTML = `
        <div class="modal-imagem">
          <img src="${obra.img}" alt="${obra.nome}" onerror="this.onerror=null; this.src='../img/imagem2.png';">
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
            <span class="info-value">${obra.dimensao || 'Não informado'}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Técnica:</span>
            <span class="info-value">${obra.tecnica || 'Não informado'}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Ano:</span>
            <span class="info-value">${obra.ano || 'Não informado'}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Material:</span>
            <span class="info-value">${obra.material || 'Não informado'}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Categorias:</span>
            <span class="info-value">${Array.isArray(obra.categoria) ? obra.categoria.join(', ') : 'Não informado'}</span>
          </div>
          <span class="info-label">Status:</span>
          <span class="info-value" style="font-weight:bold; color:${obra.status === 'vendida' ? '#dc3545' : '#28a745'}">
            ${obra.status === 'vendida' ? 'VENDIDA' : 'DISPONÍVEL'}
          </span>
        </div>
        <div class="descricao-completa">
          <h4>Descrição da Obra</h4>
          <p>${obra.desc || 'Esta obra não possui descrição.'}</p>
        </div>
        <div class="modal-actions">
  ${
    obra.status === 'vendida'
    ? `
      <div style="
        background:#dc3545;
        color:white;
        padding:15px 25px;
        border-radius:10px;
        font-weight:bold;
        display:flex;
        align-items:center;
        gap:10px;
      ">
        <i class="fas fa-lock"></i> OBRA VENDIDA — EDIÇÃO BLOQUEADA
      </div>
    `
    : `
      <a href="editar_obra2.php?id=${obra.id}" class="btn-editar-modal" style="
        background: #cc624e;
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: bold;
      ">
        <i class="fas fa-edit"></i> Editar Obra
      </a>

      <button class="btn-excluir-modal" onclick="confirmarExclusaoModal(${obra.id})" style="
        background: #dc3545;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: bold;
      ">
        <i class="fas fa-trash"></i> Excluir Obra
      </button>
    `
  }
</div>

      `;

      // Mostrar modal
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    // Função para confirmar exclusão no modal
    function confirmarExclusaoModal(obraId) {
        const obra = obras[obraId];
        if (!obra) {
            console.error('Obra não encontrada:', obraId);
            return;
        }

        Swal.fire({
            title: 'Tem certeza?',
            html: `Você está prestes a excluir a obra:<br><strong>"${obra.nome}"</strong><br><br>Esta ação não pode ser desfeita!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const dados = new URLSearchParams();
                dados.append('acao', 'excluir');
                dados.append('obra_id', obraId);

                return fetch('excluirobra.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: dados
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Erro ${response.status}: ${response.statusText}\nResposta: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Erro desconhecido do servidor');
                    }
                    return data;
                })
                .catch(error => {
                    console.error('Erro completo:', error);
                    Swal.showValidationMessage(`Falha na comunicação com o servidor: ${error.message}`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Excluída!',
                    text: 'A obra foi excluída com sucesso.',
                    icon: 'success',
                    confirmButtonColor: '#cc624e'
                }).then(() => {
                    fecharModal();
                    // Recarregar a página para atualizar a lista
                    window.location.reload();
                });
            }
        });
    }

    // Fechar modal
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

    // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');
      if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          profileDropdown.style.display =
            profileDropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function (e) {
          if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
            profileDropdown.style.display = 'none';
          }
        });

        profileDropdown.addEventListener('click', function (e) {
          e.stopPropagation();
        });
      }

      // 🔹 CORREÇÃO: Mostrar mensagem de sucesso se obra foi editada/novo
      <?php if ($obraEditada): ?>
      setTimeout(() => {
        const obraEditadaElement = document.getElementById('obra-<?php echo $obraEditada; ?>');
        if (obraEditadaElement) {
          obraEditadaElement.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center'
          });
          
          // Adicionar animação de destaque
          obraEditadaElement.style.animation = 'pulse 2s ease-in-out 3';
          setTimeout(() => {
            obraEditadaElement.style.animation = '';
          }, 6000);
        }
      }, 800);
            function atualizarBadgeMensagens() {
          const badge = document.getElementById('mensagensBadge');
          const totalNaoLidas = <?php echo $total_nao_lidas; ?>;

          if (badge) {
              if (totalNaoLidas > 0) {
                  badge.textContent = totalNaoLidas;
                  badge.style.display = 'flex';
              } else {
                  badge.style.display = 'none';
              }
          }
      }

      atualizarBadgeMensagens();
  
      <?php endif; ?>
    });
</script>
</body>
</html>