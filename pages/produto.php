<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Obras - Verseal</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

<header class="verseal-header">
  <div class="logo">Verseal</div>
  <nav>
    <a href="../index.php">Início</a>
    <a href="../pages/sobre.html">Sobre</a>
    <a href="../pages/contato.php">Contato</a>
  </nav>
</header>

<section class="hero">
  <h1>Nossas Obras</h1>
  <p>Descubra peças únicas que carregam histórias e emoções.</p>
</section>

<section class="filtros">
  <input type="text" id="busca" placeholder="Buscar obra...">
  <select id="categoria">
    <option value="">Todas categorias</option>
    <option value="escultura">Esculturas</option>
    <option value="pintura">Pinturas</option>
    <option value="joia">Joias</option>
  </select>
  <select id="preco">
    <option value="">Todos preços</option>
    <option value="0-300">Até R$ 300</option>
    <option value="300-600">R$ 300 - R$ 600</option>
    <option value="600-1000">R$ 600 - R$ 1000</option>
  </select>
</section>

<section class="produtos-grid" id="produtosGrid">
  <!-- Produtos inseridos dinamicamente pelo JS -->
</section>

<div class="paginacao">
  <button id="carregarMais">Carregar mais</button>
</div>

<footer class="verseal-footer">
  <p>© 2025 Verseal - Todos os direitos reservados</p>
</footer>

<script src="script.js"></script>
</body>
</html>