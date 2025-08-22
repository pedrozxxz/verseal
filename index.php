<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Arte e NFT</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="./css/style.css" />
</head>

<body>

  <!-- HEADER -->
  <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="#">Início</a>
      <a href="./pages/produto.php">Obras</a>
      <a href="./pages/sobre.html">Sobre</a>
      <a href="./pages/contato.php">Contato</a>
      <a href="./pages/login.php">Login</a>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">
      <h1>Arte que Transforma.</h1>
      <p>Explore NFTs e obras únicas feitas à mão.</p>
      <a href="#produtos" class="btn-destaque">Ver Obras</a>
    </div>
    <div class="hero-gallery">
      <img src="./img/nft-beard.webp" alt="Arte destaque 1" />
      <img src="./img/nft-beard.webp" alt="Arte destaque 2" />
      <img src="./img/nft-beard.webp" alt="Arte destaque 3" />
    </div>
  </section>

  <!-- PRODUTOS -->
  <section id="produtos" class="produtos">
    <h2>Obras em Destaque</h2>
    <div class="galeria">
      <div class="card">
        <img src="./img/midia.jfif" alt="Produto 1" />
        <h3>"Noite de Safira"</h3>
        <p>Arte digital - R$ 120</p>
      </div>
      <div class="card">
        <img src="./img/todos.jfif" alt="Produto 2" />
        <h3>"Princesa Das Sombras"</h3>
        <p>Arte Manual - R$ 200</p>
      </div>
      <div class="card">
        <img src="./img/desenho.jfif" alt="Produto 3" />
        <h3>"Guardiões Da Lâmina"</h3>
        <p>Arte NFT exclusiva - R$ 165</p>
      </div>
    </div>
  </section>

  <!-- SOBRE COM PARTÍCULAS -->
  <section class="sobre" id="sobre">
    <div id="particles-sobre"></div>
    <div class="conteudo-sobre">
      <h2>Sobre a Verseal</h2>
      <p>
        Unindo o digital ao artesanal, a <strong>Verseal</strong> é o elo entre o <em>futuro</em> e o <em>feito à
          mão</em>. Aqui, artistas expressam sua alma em NFTs e criações únicas, para colecionadores que buscam mais do
        que uma obra: uma <span class="destaque">conexão real</span>.
      </p>
      <p>
        Somos mais do que um marketplace. Somos uma galeria viva de expressão, movimento e autenticidade.
      </p>
    </div>
  </section>

  <!-- RODAPÉ -->
  <footer>
    <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
    <div class="social">
      <a href="#"><img src="./img/instagram.png" alt="Instagram" /></a>
      <a href="#"><img src="./img/linkedin_3536505.png" alt="Linkedin" /></a>
      <a href="#"><img src="./img/whatsapp_3536445.png" alt="Whatsapp" /></a>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
  <script>
    VANTA.WAVES({
  el: "#sobre",
  mouseControls: true,
  touchControls: true,
  minHeight: 200.00,
  minWidth: 200.00,
  scale: 1.0,
  scaleMobile: 1.0,
  color: 0x8a7360, 
  shininess: 40.0,
  waveHeight: 20.0,
  waveSpeed: 0.5,
  zoom: 1
})
  </script>
</body>

</html>