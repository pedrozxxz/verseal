<?php
session_start();
$usuarioLogado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Adicionar Obras - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/adicionar-obras.css" />
  <style>
    .btn-salvar{
      margin-left: 400px;
    }
  </style>
</head>

<body>
  <header>
    <div class="logo">Verseal</div>
    
    <nav>
      <a href="artistahome.php"><i class="fas fa-home"></i> Início</a>
      <a href="#"><i class="fas fa-palette"></i> Obras</a>
      <a href="#"><i class="fas fa-user"></i> Quem eu sou?</a>

      
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
            <a href="#" class="dropdown-item">
              <i class="fas fa-user-circle"></i> Meu Perfil
            </a>
            <a href="#" class="dropdown-item">
              <i class="fas fa-shopping-bag"></i> Minhas Vendas
            </a>
            <a href="#" class="dropdown-item">
              <i class="fas fa-heart"></i> Favoritos
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item logout-btn">
              <i class="fas fa-sign-out-alt"></i> Sair
            </a>
          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-sign-in-alt"></i> Fazer Login
            </a>
            <a href="#" class="dropdown-item">
              <i class="fas fa-user-plus"></i> Cadastrar
            </a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>

  <!-- FORMULÁRIO ADICIONAR OBRAS -->
  <section class="adicionar-obras">
    <div class="container">
      <h1>ADICIONAR OBRAS</h1>
      
      <form class="form-obras" id="form-obras">
        <div class="form-grid">
          <div class="form-column">
            <div class="form-group">
              <label for="nome-obra">Nome da Obra</label>
              <input type="text" id="nome-obra" placeholder="Digite..." required>
            </div>
            
            <div class="form-group">
              <label for="preco">Preço</label>
              <input type="text" id="preco" placeholder="Digite..." required>
            </div>
            
            <div class="form-group">
              <label for="tecnica">Técnica/Estilo</label>
              <select id="tecnica" required>
                <option value="" disabled selected>Selecione</option>
                <option value="manual">Manual</option>
                <option value="nft">NFT</option>
                <option value="mesa-digital">Mesa-digital</option>
                <option value="pintura">Pintura</option>
                <option value="escultura">Escultura</option>
                <option value="fotografia">Fotografia</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="dimensao">Dimensão</label>
              <input type="text" id="dimensao" placeholder="Digite...">
            </div>
            
            <div class="form-group">
              <label for="data-criacao">Data de Criação</label>
              <input type="date" id="data-criacao" required>
            </div>
            
            <div class="form-group">
              <label for="palavras-chave">Palavras-chaves</label>
              <input type="text" id="palavras-chave" placeholder='EX: "Abstrato", "paisagem"'>
            </div>
          </div>
          
          <div class="form-column">
            <div class="upload-area" id="upload-area">
              <div class="upload-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <h3>INSERIR IMAGEM</h3>
                <p>Arraste e solte ou clique para fazer upload</p>
                <span>PNG, JPG, JPEG até 10MB</span>
              </div>
              <input type="file" id="imagem-obra" accept="image/*" hidden>
            </div>
            <div class="image-preview" id="image-preview"></div>
          </div>
        </div>
        
        <button type="submit" class="btn-salvar">
          <i class="fas fa-save"></i>
          SALVAR
        </button>
      </form>
    </div>
  </section>

  <!-- RODAPÉ -->
  <footer>
    <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
    <div class="social">
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-linkedin-in"></i></a>
      <a href="#"><i class="fab fa-whatsapp"></i></a>
    </div>
  </footer>

  <script src="../js/adicionar-obras.js"></script>
</body>
</html>
