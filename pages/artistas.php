<?php
session_start();

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se usuário está logado (cliente ou artista)
$usuarioLogado = null;
$tipoUsuario = null;

if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
} elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}

// Buscar apenas artistas sem contar obras
$sql_artistas = "SELECT * FROM artistas WHERE ativo = 1 ORDER BY nome ASC";
$result_artistas = $conn->query($sql_artistas);
$artistas = [];

if ($result_artistas && $result_artistas->num_rows > 0) {
    while ($artista = $result_artistas->fetch_assoc()) {
        // Ajustar o caminho da imagem
        $foto_perfil = '';
        if (!empty($artista['imagem_perfil'])) {
            if (strpos($artista['imagem_perfil'], 'http') === 0) {
                $foto_perfil = $artista['imagem_perfil'];
            } else {
                $foto_perfil = (strpos($artista['imagem_perfil'], '../') === 0) 
                    ? $artista['imagem_perfil'] 
                    : '../' . $artista['imagem_perfil'];
            }
        }
        
        $artistas[] = [
            'id' => $artista['id'],
            'nome' => $artista['nome'],
            'idade' => $artista['idade'] ?? '',
            'descricao' => $artista['descricao'] ?? $artista['biografia'] ?? '',
            'biografia' => $artista['biografia'] ?? $artista['descricao'] ?? '',
            'telefone' => $artista['telefone'] ?? '',
            'email' => $artista['email'] ?? '',
            'instagram' => $artista['instagram'] ?? '',
            'cor_gradiente' => $artista['cor_gradiente'] ?? 'linear-gradient(135deg, #e07b67, #cc624e)',
            'icone' => $artista['icone'] ?? 'fas fa-paint-brush',
            'foto_perfil' => $foto_perfil,
            'total_obras' => 0
        ];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Artistas - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/artista.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
   .btn-mensagem-artista {
      display: inline-block;
      margin-top: 12px;
      background: linear-gradient(135deg, #28a745, #20c997);
      color: #fff;
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.25s ease;
      border: none;
      cursor: pointer;
      font-size: 0.9rem;
    }

    .btn-mensagem-artista:hover {
      background: linear-gradient(135deg, #20c997, #28a745);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .btn-mensagem-artista i {
      margin-right: 8px;
    }

    .artista-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 15px;
    }

    /* Biografia Style */
    .artista-biografia {
      margin-top: 15px;
      padding: 15px;
      background: #f9f9f9;
      border-radius: 10px;
      border-left: 4px solid #e07b67;
    }

    .biografia-titulo {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .biografia-titulo i {
      color: #e07b67;
    }

    .biografia-texto {
      color: #555;
      line-height: 1.6;
      font-size: 0.95rem;
    }

    /* Modal de Mensagem */
    .modal-mensagem {
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

    .modal-mensagem.active {
      display: flex;
    }

    .modal-conteudo-mensagem {
      background: white;
      border-radius: 15px;
      max-width: 500px;
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

    .modal-header-mensagem {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 25px;
      border-bottom: 1px solid #eee;
    }

    .modal-header-mensagem h2 {
      font-family: 'Playfair Display', serif;
      color: #cc624e;
      margin: 0;
      font-size: 1.5rem;
    }

    .btn-fechar-modal {
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #666;
      cursor: pointer;
      padding: 5px;
      transition: color 0.3s;
    }

    .btn-fechar-modal:hover {
      color: #cc624e;
    }

    .modal-body-mensagem {
      padding: 25px;
    }

    .form-mensagem {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .form-mensagem label {
      font-weight: 600;
      color: #333;
    }

    .form-mensagem input,
    .form-mensagem textarea {
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s;
      font-family: 'Open Sans', sans-serif;
    }

    .form-mensagem input:focus,
    .form-mensagem textarea:focus {
      outline: none;
      border-color: #cc624e;
    }

    .form-mensagem textarea {
      height: 120px;
      resize: vertical;
    }

    .btn-enviar-mensagem {
      background: #28a745;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
      font-size: 1rem;
    }

    .btn-enviar-mensagem:hover {
      background: #20c997;
    }

    .btn-enviar-mensagem:disabled {
      background: #6c757d;
      cursor: not-allowed;
    }

    /* Estilos para o dropdown corrigido */
    .profile-dropdown {
      position: relative;
      display: inline-block;
    }
    
    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background: white;
      min-width: 220px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      border-radius: 8px;
      z-index: 1000;
      padding: 10px 0;
    }
    
    .dropdown-content.show {
      display: block;
    }
    
    .user-info {
      padding: 10px 15px;
      border-bottom: 1px solid #eee;
    }
    
    .user-info p {
      margin: 0;
      font-size: 0.9rem;
      color: #333;
    }
    
    .dropdown-item {
      display: flex;
      align-items: center;
      padding: 8px 15px;
      text-decoration: none;
      color: #333;
      transition: background 0.3s;
    }
    
    .dropdown-item:hover {
      background: #f8f9fa;
    }
    
    .dropdown-item i {
      margin-right: 10px;
      width: 16px;
      text-align: center;
    }
    
    .dropdown-divider {
      height: 1px;
      background: #eee;
      margin: 5px 0;
    }
    
    .logout-btn {
      color: #dc3545;
    }
    
    .logout-btn:hover {
      background: #f8d7da;
    }

    /* CORREÇÃO: Estilos para as imagens dos artistas */
    .artista-imagem {
        height: 250px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Remove o fundo gradiente quando tem imagem */
    .artista-imagem:has(img[src*="/"]) {
        background: transparent !important;
    }

    /* Mantém o fundo apenas para ícones (quando não tem imagem) */
    .artista-imagem:not(:has(img[src*="/"])) {
        background: linear-gradient(135deg, #e07b67, #cc624e);
    }

    .artista-imagem img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
    }

    .card-artista:hover .artista-imagem img {
        transform: scale(1.05);
    }

    .icone-fallback {
        font-size: 4rem;
        color: white;
        opacity: 0.8;
    }

    /* Estilos existentes para a galeria */
    .galeria-artistas {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 30px;
      padding: 20px 0;
    }

    .card-artista {
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-artista:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .artista-info {
      padding: 25px;
    }

    .artista-nome {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem;
      color: #333;
      margin-bottom: 8px;
    }

    .artista-idade {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .total-obras {
      background: #f8f9fa;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      color: #666;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      margin-bottom: 15px;
    }

    .artista-descricao {
      color: #555;
      line-height: 1.5;
      margin-bottom: 15px;
      font-size: 0.95rem;
    }

    .artista-contatos {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 15px;
    }

    .contato-item {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #666;
      font-size: 0.9rem;
    }

    .contato-item i {
      width: 16px;
      color: #e07b67;
    }

    .nenhum-artista {
      text-align: center;
      padding: 60px 20px;
      color: #666;
    }

    .nenhum-artista h3 {
      margin: 15px 0 10px 0;
      color: #333;
    }

    /* Hero section */
    .hero-artistas {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 100px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
      border-radius: 0 0 20px 20px;
    }

    .hero-artistas-content {
      flex: 1;
      max-width: 600px;
    }

    .hero-artistas-content h1 {
      font-family: 'Playfair Display', serif;
      font-size: 3rem;
      margin-bottom: 20px;
    }

    .hero-artistas-content p {
      font-size: 1.2rem;
      margin-bottom: 30px;
      opacity: 0.9;
    }

    .btn-destaque {
      background: white;
      color: #667eea;
      padding: 12px 30px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-destaque:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
    }
  </style>
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
              <p>
                Seja bem-vindo, 
                <span id="user-name">
                  <?php 
                  if ($tipoUsuario === "cliente") {
                    echo htmlspecialchars(is_array($usuarioLogado) ? $usuarioLogado['nome'] : $usuarioLogado);
                  } elseif ($tipoUsuario === "artista") {
                    echo htmlspecialchars(is_array($usuarioLogado) ? ($usuarioLogado['nome_artistico'] ?? $usuarioLogado['nome']) : $usuarioLogado);
                  }
                  ?>
                </span>!
              </p>
            </div>
            <div class="dropdown-divider"></div>

            <?php if ($tipoUsuario === "cliente"): ?>
              <a href="./perfilCliente.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
              <a href="./favoritos.php" class="dropdown-item"><i class="fas fa-heart"></i> Favoritos</a>
            <?php elseif ($tipoUsuario === "artista"): ?>
              <a href="./artistahome.php" class="dropdown-item"><i class="fas fa-palette"></i> Meu Perfil</a>
            <?php endif; ?>

            <div class="dropdown-divider"></div>
            <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>

          <?php else: ?>
            <div class="user-info">
              <p>Faça login para acessar seu perfil</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="./login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
            <a href="./cadastro.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>

  <section class="hero-artistas">
    <div class="hero-artistas-content">
      <h1>Nossos Artistas</h1>
      <p>Conheça os talentos por trás das obras que transformam.</p>
      <a href="#artistas" class="btn-destaque">Explorar Talentos</a>
    </div>
    <div class="hero-artistas-imagem">
      <div class="arte-abstrata"></div>
    </div>
  </section>

  <!-- SEÇÃO ARTISTAS -->
  <section class="artistas" id="artistas" style="padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
      <h2 style="text-align: center; font-family: 'Playfair Display', serif; color: #333; margin-bottom: 20px;">Conheça Nossos Talentos</h2>
      <p style="text-align: center; color: #666; font-size: 1.1rem; margin-bottom: 40px;">
        Artistas independentes que buscam autonomia no mercado artístico através da Verseal
      </p>
      
      <div class="galeria-artistas">
        <?php if (empty($artistas)): ?>
          <div class="nenhum-artista">
            <i class="fas fa-palette" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
            <h3>Nenhum artista cadastrado</h3>
            <p>Em breve teremos artistas incríveis para apresentar!</p>
          </div>
        <?php else: ?>
          <?php foreach ($artistas as $artista): ?>
            <div class="card-artista">
              <div class="artista-imagem">
                <?php if (!empty($artista['foto_perfil'])): ?>
                  <img src="<?php echo htmlspecialchars($artista['foto_perfil']); ?>" 
                       alt="<?php echo htmlspecialchars($artista['nome']); ?>"
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class="icone-fallback" style="display: none;">
                    <i class="<?php echo $artista['icone']; ?>"></i>
                  </div>
                <?php else: ?>
                  <div class="icone-fallback">
                    <i class="<?php echo $artista['icone']; ?>"></i>
                  </div>
                <?php endif; ?>
              </div>

              <div class="artista-info">
                <h3 class="artista-nome"><?php echo htmlspecialchars($artista['nome']); ?></h3>
                
                <?php if (!empty($artista['idade'])): ?>
                  <p class="artista-idade"><?php echo htmlspecialchars($artista['idade']); ?> anos</p>
                <?php endif; ?>

                <?php if (!empty($artista['total_obras'])): ?>
                  <div class="total-obras">
                    <i class="fas fa-palette"></i> <?php echo $artista['total_obras']; ?> obras
                  </div>
                <?php endif; ?>
                
                <!-- Descrição curta -->
                <?php if (!empty($artista['descricao'])): ?>
                  <p class="artista-descricao">
                    <?php echo htmlspecialchars($artista['descricao']); ?>
                  </p>
                <?php endif; ?>

                <div class="artista-contatos">
                  <?php if (!empty($artista['email'])): ?>
                    <div class="contato-item">
                      <i class="fas fa-envelope"></i>
                      <span><?php echo htmlspecialchars($artista['email']); ?></span>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($artista['instagram'])): ?>
                    <div class="contato-item">
                      <i class="fab fa-instagram"></i>
                      <span><?php echo htmlspecialchars($artista['instagram']); ?></span>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($artista['telefone'])): ?>
                    <div class="contato-item">
                      <i class="fas fa-phone"></i>
                      <span><?php echo htmlspecialchars($artista['telefone']); ?></span>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="artista-actions">
                  <?php if ($usuarioLogado): ?>
                    <button class="btn-mensagem-artista" onclick="abrirModalMensagem(<?php echo $artista['id']; ?>, '<?php echo htmlspecialchars($artista['nome']); ?>')">
                      <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                  <?php else: ?>
                    <a href="login.php" class="btn-mensagem-artista">
                      <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <script>
    // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');
      
      if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          profileDropdown.classList.toggle('show');
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function (e) {
          if (!profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
          }
        });

        // Prevenir fechamento ao clicar dentro do dropdown
        profileDropdown.addEventListener('click', function (e) {
          e.stopPropagation();
        });
      }
    });

    function abrirModalMensagem(artistaId, artistaNome) {
      alert('Funcionalidade de mensagem para: ' + artistaNome);
      // Aqui você pode implementar o modal de mensagem
    }
  </script>
</body>
</html>