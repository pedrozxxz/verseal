<!-- adicionar uma caixa de seleção de opções de obras, especficamente do botão
 de editar obras da home -->

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editar Obra - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/adicionar-obras.css" />
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="logo">Verseal</div>
    
    <nav>
      <a href="artistahome.php"><i class="fas fa-home"></i> Início</a>
      <a href="artistasobra.php"><i class="fas fa-palette"></i> Obras</a>
      <a href="artistabiografia.php"><i class="fas fa-user"></i> Quem eu sou?</a>

      <div class="profile-dropdown">
        <a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
        <div class="dropdown-content" id="profile-dropdown">
          <div class="user-info">
            <p>Faça login para acessar seu perfil</p>
          </div>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
          <a href="#" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
        </div>
      </div>
    </nav>
  </header>

  <!-- FORMULÁRIO EDITAR OBRAS -->
  <section class="adicionar-obras">
  <div class="container">
    <h1>EDITAR OBRAS</h1>

    <form class="form-obras" id="form-obras">
      <div class="form-grid">
        <div class="form-column">

          <!-- Seleção da Obra -->
          <div class="form-group">
            <label for="select-obra">Selecione a Obra</label>
            <select id="select-obra">
              <option value="1" selected>Pôr do Sol</option>
              <option value="2">Natureza Viva</option>
              <option value="3">Abstrato Azul</option>
            </select>
          </div>

          <div class="form-group">
            <label for="nome-obra">Nome da Obra</label>
            <input type="text" id="nome-obra" placeholder="Digite..." value="Pôr do Sol">
          </div>

          <div class="form-group">
            <label for="preco">Preço</label>
            <input type="text" id="preco" placeholder="Digite..." value="1500">
          </div>

          <div class="form-group">
            <label for="tecnica">Técnica/Estilo</label>
            <select id="tecnica">
              <option value="manual">Manual</option>
              <option value="nft">NFT</option>
              <option value="mesa-digital">Mesa Digital</option>
              <option value="pintura" selected>Pintura</option>
              <option value="escultura">Escultura</option>
              <option value="fotografia">Fotografia</option>
            </select>
          </div>

          <div class="form-group">
            <label for="dimensao">Dimensão</label>
            <input type="text" id="dimensao" placeholder="Digite..." value="70x50cm">
          </div>

          <div class="form-group">
            <label for="data-criacao">Data de Criação</label>
            <input type="date" id="data-criacao" value="2025-01-01">
          </div>

          <div class="form-group">
            <label for="palavras-chave">Palavras-chaves</label>
            <input type="text" id="palavras-chave" placeholder='EX: "Abstrato", "Paisagem"' value="Paisagem, Luz">
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

          <div class="image-preview" id="image-preview">
            <img src="../imagens/por-do-sol.jpg" alt="Imagem da obra">
          </div>

          <!-- Novo campo para trocar imagem -->
          <div class="form-group">
            <label for="nova-imagem">Trocar Imagem</label>
            <input type="file" id="nova-imagem" accept="image/*">
          </div>
        </div>
      </div>

     <div class="botoes-acoes">
  <button type="button" class="btn-salvar" onclick="window.location.href='artistasobra.php'">
    <i class="fas fa-save"></i> SALVAR
  </button>

        <button type="button" class="btn-excluir">
          <i class="fas fa-trash"></i> EXCLUIR
        </button>
      </div>
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

  <script>
    // Dropdown do perfil
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');
    profileIcon.addEventListener('click', function(e){
      e.preventDefault();
      profileDropdown.classList.toggle('show');
    });
    document.addEventListener('click', function(e){
      if(!profileDropdown.contains(e.target) && e.target !== profileIcon){
        profileDropdown.classList.remove('show');
      }
    });

    // Upload de imagem
    const uploadArea = document.getElementById('upload-area');
    const imageInput = document.getElementById('imagem-obra');
    const imagePreview = document.getElementById('image-preview');

    uploadArea.addEventListener('click', () => imageInput.click());
    imageInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if(file){
        const reader = new FileReader();
        reader.onload = function(event){
          imagePreview.innerHTML = `<img src="${event.target.result}" alt="Imagem da obra">`;
        };
        reader.readAsDataURL(file);
      }
    });
  </script>



<script>
  const obras = {
    1: {
      nome: "Pôr do Sol",
      preco: "1500",
      tecnica: "pintura",
      dimensao: "70x50cm",
      data: "2025-01-01",
      palavras: "Paisagem, Luz",
      imagem: "../imagens/por-do-sol.jpg"
    },
    2: {
      nome: "Natureza Viva",
      preco: "1800",
      tecnica: "manual",
      dimensao: "60x80cm",
      data: "2024-09-15",
      palavras: "Natureza, Realismo",
      imagem: "../imagens/natureza.jpg"
    },
    3: {
      nome: "Abstrato Azul",
      preco: "2000",
      tecnica: "mesa-digital",
      dimensao: "50x70cm",
      data: "2025-02-10",
      palavras: "Abstrato, Azul",
      imagem: "../imagens/abstrato.jpg"
    }
  };

  const selectObra = document.getElementById("select-obra");
  const nomeObra = document.getElementById("nome-obra");
  const preco = document.getElementById("preco");
  const tecnica = document.getElementById("tecnica");
  const dimensao = document.getElementById("dimensao");
  const dataCriacao = document.getElementById("data-criacao");
  const palavras = document.getElementById("palavras-chave");
  const imagePreview = document.getElementById("image-preview");

  selectObra.addEventListener("change", () => {
    const obraSelecionada = obras[selectObra.value];

    nomeObra.value = obraSelecionada.nome;
    preco.value = obraSelecionada.preco;
    tecnica.value = obraSelecionada.tecnica;
    dimensao.value = obraSelecionada.dimensao;
    dataCriacao.value = obraSelecionada.data;
    palavras.value = obraSelecionada.palavras;
    imagePreview.innerHTML = `<img src="${obraSelecionada.imagem}" alt="Imagem da obra">`;
  });

  // Pré-visualização da nova imagem
  const novaImagem = document.getElementById("nova-imagem");
  novaImagem.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        imagePreview.innerHTML = `<img src="${event.target.result}" alt="Nova imagem da obra">`;
      };
      reader.readAsDataURL(file);
    }
  });
</script>

</body>
</html>
