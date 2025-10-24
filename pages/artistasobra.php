<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;

// Lista de produtos disponíveis
$produtos = [
    1 => ["id"=>1,"img"=>"../img/imagem2.png","nome"=>"Obra da Daniele","artista"=>"Daniele Oliveira","preco"=>199.99,"desc"=>"Desenho realizado por Stefani e Daniele, feito digitalmente e manualmente.","dimensao"=>"21 x 29,7cm (Manual) / 390cm x 522cm (Digital)","tecnica"=>"Técnica mista: digital e manual","ano"=>2024,"material"=>"Tinta acrílica e digital","categoria"=>["manual","digital","colorido"]],
    2 => ["id"=>2,"img"=>"../img/imagem9.png","nome"=>"Obra da Stefani","artista"=>"Stefani Correa","preco"=>188.99,"desc"=>"Desenho realizado com técnica mista.","dimensao"=>"42 x 59,4cm","tecnica"=>"Técnica mista","ano"=>2024,"material"=>"Nanquim e aquarela","categoria"=>["manual","colorido"]],
    3 => ["id"=>3,"img"=>"../img/imagem2.png","nome"=>"Obra Moderna","artista"=>"Daniele Oliveira","preco"=>250.00,"desc"=>"Arte contemporânea com técnicas inovadoras.","dimensao"=>"50 x 70cm","tecnica"=>"Pintura digital","ano"=>2024,"material"=>"Digital - alta resolução","categoria"=>["digital","colorido"]],
    4 => ["id"=>4,"img"=>"../img/imagem2.png","nome"=>"Paisagem Expressionista","artista"=>"Stefani Correa","preco"=>179.99,"desc"=>"Paisagem com cores vibrantes e traços expressionistas","dimensao"=>"60 x 80cm","tecnica"=>"Expressionismo","ano"=>2024,"material"=>"Óleo sobre tela","categoria"=>["manual","colorido"]],
    5 => ["id"=>5,"img"=>"../img/imagem2.png","nome"=>"Abstração Colorida","artista"=>"Lucas Andrade","preco"=>159.90,"desc"=>"Obra abstrata com paleta de cores vibrantes","dimensao"=>"40 x 60cm","tecnica"=>"Abstração","ano"=>2024,"material"=>"Acrílica sobre tela","categoria"=>["manual","colorido"]],
    6 => ["id"=>6,"img"=>"../img/imagem2.png","nome"=>"Figura Humana","artista"=>"Mariana Santos","preco"=>220.00,"desc"=>"Estudo da figura humana em movimento","dimensao"=>"70 x 100cm","tecnica"=>"Figurativo","ano"=>2024,"material"=>"Carvão e pastel","categoria"=>["manual","preto e branco"]],
    7 => ["id"=>7,"img"=>"../img/imagem2.png","nome"=>"Natureza Morta","artista"=>"Rafael Costa","preco"=>145.50,"desc"=>"Natureza morta com elementos clássicos","dimensao"=>"50 x 70cm","tecnica"=>"Realismo","ano"=>2024,"material"=>"Óleo sobre tela","categoria"=>["manual","colorido"]],
    8 => ["id"=>8,"img"=>"../img/imagem2.png","nome"=>"Cidade Noturna","artista"=>"Camila Rocha","preco"=>189.99,"desc"=>"Panorama urbano noturno","dimensao"=>"80 x 120cm","tecnica"=>"Urban sketching","ano"=>2024,"material"=>"Tinta acrílica","categoria"=>["manual","colorido"]],
    9 => ["id"=>9,"img"=>"../img/imagem2.png","nome"=>"Abstração Minimalista","artista"=>"João Almeida","preco"=>249.00,"desc"=>"Obra minimalista com formas puras","dimensao"=>"60 x 60cm","tecnica"=>"Minimalismo","ano"=>2024,"material"=>"Acrílica sobre MDF","categoria"=>["manual","colorido"]],
    10 => ["id"=>10,"img"=>"../img/imagem2.png","nome"=>"Flores Silvestres","artista"=>"Bianca Freitas","preco"=>120.00,"desc"=>"Composição floral com cores suaves","dimensao"=>"40 x 50cm","tecnica"=>"Aquarela","ano"=>2024,"material"=>"Aquarela sobre papel","categoria"=>["manual","colorido"]],
    11 => ["id"=>11,"img"=>"../img/imagem2.png","nome"=>"Mar em Movimento","artista"=>"Felipe Duarte","preco"=>199.90,"desc"=>"Representação do movimento das ondas","dimensao"=>"90 x 120cm","tecnica"=>"Abstração lírica","ano"=>2024,"material"=>"Óleo sobre tela","categoria"=>["manual","colorido"]],
    12 => ["id"=>12,"img"=>"../img/imagem2.png","nome"=>"Retrato em Preto e Branco","artista"=>"Ana Clara","preco"=>134.99,"desc"=>"Retrato clássico em técnica monocromática","dimensao"=>"50 x 70cm","tecnica"=>"Realismo","ano"=>2024,"material"=>"Grafite e carvão","categoria"=>["manual","preto e branco"]],
];

// Filtros e ordenação
$filtroArtista = $_GET['artista'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? [];
$ordenacao = $_GET['ordenacao'] ?? 'preco_asc';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['buscar_artista'])){
    $filtroArtista = $_POST['artista'] ?? '';
    header('Location: ?artista='.urlencode($filtroArtista));
    exit;
}

$produtosFiltrados = $produtos;

if(!empty($filtroArtista)){
    $produtosFiltrados = array_filter($produtosFiltrados, fn($p)=> stripos($p['artista'],$filtroArtista)!==false);
}

if(!empty($filtroCategoria) && is_array($filtroCategoria)){
    $produtosFiltrados = array_filter($produtosFiltrados, fn($p)=> count(array_intersect($p['categoria'],$filtroCategoria))>0);
}

if($ordenacao==='preco_asc'){ usort($produtosFiltrados, fn($a,$b)=> $a['preco']<=>$b['preco']); }
elseif($ordenacao==='preco_desc'){ usort($produtosFiltrados, fn($a,$b)=> $b['preco']<=>$a['preco']); }
elseif($ordenacao==='recentes'){ usort($produtosFiltrados, fn($a,$b)=> $b['id']<=>$a['id']); }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verseal - Obras de Arte</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
<link rel="stylesheet" href="../css/style.css">
<style>
/* Título da página */
.titulo-pagina {
  text-align: center;
  margin-bottom: 30px;
  font-family: "Playfair Display", serif;
  font-size: 2.2rem;
  color: #cc624e;
}

/* Botão de filtro igual ao site */
.btn-aplicar-filtros {
  background: linear-gradient(135deg, #cc624e, #b34f3e);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 14px;
  margin-top: 10px;
  width: 100%;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-aplicar-filtros:hover {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(204,98,78,0.3);
}
.barra-filtros-topo {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 30px;
  padding: 10px 50px;
}

/* Botões de ordenação */
.btn-ordenacao {
  background: linear-gradient(135deg, #cc624e, #b34f3e);
  color: #fff;
  padding: 8px 14px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.25s ease;
  margin-right: 8px;
  display: inline-block;
}

.btn-ordenacao:hover {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(204,98,78,0.3);
}

/* Campo de busca */
.busca-artista {
  display: flex;
  gap: 6px;
}

.busca-artista input {
  border-radius: 8px;
  border: 1px solid #ccc;
  padding: 8px 12px;
  outline: none;
  transition: border 0.2s, box-shadow 0.2s;
  min-width: 180px;
}

.busca-artista input:focus {
  border-color: #cc624e;
  box-shadow: 0 0 6px rgba(204,98,78,0.3);
}

/* Botão de busca */
.btn-buscar {
  background: linear-gradient(135deg, #cc624e, #b34f3e);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-buscar:hover {
  background: linear-gradient(135deg, #e07b67, #cc624e);
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(204,98,78,0.3);
}
.conteudo-obras{display:grid;grid-template-columns:260px 1fr;gap:30px;margin:40px;}
.filtro{background:#fafafa;padding:20px;border-radius:12px;box-shadow:0 3px 6px rgba(0,0,0,0.05);}
.filtro-box label{display:flex;align-items:center;gap:8px;margin-bottom:8px;cursor:pointer;padding:4px 6px;border-radius:6px;}
.lista-obras{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:25px;}
.obra-card{background:#fff;border-radius:12px;box-shadow:0 3px 6px rgba(0,0,0,0.1);text-align:center;padding:15px;}
.obra-card img{width:100%;border-radius:10px;margin-bottom:12px;}
.obra-card h4{font-family:"Playfair Display",serif;color:#333;margin:5px 0;}
.preco-obra{font-weight:bold;color:#cc624e;margin:10px 0;display:block;}
.btn-detalhes{background:linear-gradient(135deg,#cc624e,#b34f3e);color:#fff;border:none;border-radius:10px;width:100%;padding:10px;cursor:pointer;font-weight:600;}
.btn-detalhes:hover{background:linear-gradient(135deg,#e07b67,#cc624e);}
</style>
</head>
<body>

<!-- INSERINDO A NAVBAR DO SEU CÓDIGO -->
<header>
  <div class="logo">Verseal</div>
  
  <nav>
    <a href="artistahome.php">Início</a>
    <a href="./artistasobra.php">Obras</a>
    <a href="./artistabiografia.php">Artistas</a>
    
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

    <a href="./carrinho.php" class="icon-link" id="cart-icon"><i class="fas fa-shopping-cart"></i></a>
    <div class="profile-dropdown">
      <a href="./perfil.php" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
      <div class="dropdown-content" id="profile-dropdown">
        <?php if ($usuarioLogado): ?>
          <div class="user-info"><p>Seja bem-vindo, <?php echo htmlspecialchars($usuarioLogado); ?>!</p></div>
          <div class="dropdown-divider"></div>
          <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
          <a href="./minhas-compras.php" class="dropdown-item"><i class="fas fa-shopping-bag"></i> Minhas Compras</a>
          <a href="./favoritos.php" class="dropdown-item"><i class="fas fa-heart"></i> Favoritos</a>
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

<main class="pagina-obras">
  <h1 class="titulo-pagina">Obras de Arte</h1>

 <div class="barra-filtros-topo">
  <div class="ordenacao">
    <a href="?ordenacao=preco_asc" class="btn-ordenacao">Menor Preço</a>
    <a href="?ordenacao=preco_desc" class="btn-ordenacao">Maior Preço</a>
    <a href="?ordenacao=recentes" class="btn-ordenacao">Mais Recentes</a>
  </div>

  <form method="POST" class="busca-artista">
    <input type="text" name="artista" placeholder="Buscar artista..." value="<?php echo htmlspecialchars($filtroArtista); ?>">
    <button type="submit" name="buscar_artista" class="btn-buscar">Buscar</button>
  </form>
</div>
<div class="conteudo-obras">
    <aside class="filtro">
      <h3>Filtros</h3>
      <form method="GET">
        <div class="filtro-box">
          <label><input type="checkbox" name="categoria[]" value="manual" <?php echo in_array('manual',$filtroCategoria) ? 'checked' : ''; ?>> Manual</label>
          <label><input type="checkbox" name="categoria[]" value="digital" <?php echo in_array('digital',$filtroCategoria) ? 'checked' : ''; ?>> Digital</label>
          <label><input type="checkbox" name="categoria[]" value="colorido" <?php echo in_array('colorido',$filtroCategoria) ? 'checked' : ''; ?>> Colorido</label>
          <label><input type="checkbox" name="categoria[]" value="preto e branco" <?php echo in_array('preto e branco',$filtroCategoria) ? 'checked' : ''; ?>> Preto e Branco</label>
        </div>
        <!-- Botão estilizado igual ao site -->
        <button type="submit" class="btn-aplicar-filtros">Aplicar Filtros</button>
      </form>
      <button onclick="window.location.href='adicionarobra.php'" class="btn-aplicar-filtros">Adicionar Obra</button>
    </aside>

     <section class="lista-obras">
      <?php foreach ($produtosFiltrados as $produto): ?>
        <div class="obra-card">
          <img src="<?php echo $produto['img']; ?>" alt="<?php echo $produto['nome']; ?>">
          <h4><?php echo $produto['nome']; ?></h4>
          <p><?php echo $produto['artista']; ?></p>
          <span class="preco-obra">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
          <button class="btn-detalhes" onclick="irParaEdicao(<?php echo $produto['id']; ?>)">Editar Detalhes</button>
        </div>
      <?php endforeach; ?>
    </section>
  </div>
</main>

<script>
function irParaEdicao(id){
  window.location.href=`editar-obra.html?id=${id}`;
}
</script>

</body>
</html>
