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
              <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
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
    <div class="hero-overlay"></div>
    <div class="hero-artistas-content">
      <h1>Nossos Artistas</h1>
      <p>Conheça os talentos por trás das obras que transformam.</p>
      <a href="#artistas" class="btn-destaque">Explorar Talentos</a>
    </div>
  </section>

  <!-- SEÇÃO ARTISTAS -->
  <section class="artistas" id="artistas" style="padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
      <h2 style="text-align: center; font-family: 'Playfair Display', serif; color: #333; margin-bottom: 20px;">Conheça
        Nossos Talentos</h2>
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
                    <button class="btn-mensagem-artista"
                      onclick="abrirModalMensagem(<?php echo $artista['id']; ?>, '<?php echo htmlspecialchars($artista['nome']); ?>')">
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

<!-- FOOTER -->
    <footer>
        <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
        <div class="social">
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-whatsapp"></i></a>
        </div>
    </footer>

</html>