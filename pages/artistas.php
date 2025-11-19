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
    /* Estilos do Modal */
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

    .modal-mensagem .modal-conteudo {
      background: white;
      border-radius: 15px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      animation: modalAppear 0.3s ease;
    }

    @keyframes modalAppear {
      from { opacity: 0; transform: scale(0.8); }
      to { opacity: 1; transform: scale(1); }
    }

    .modal-mensagem .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 25px;
      border-bottom: 1px solid #eee;
    }

    .modal-mensagem .modal-header h2 {
      font-family: 'Playfair Display', serif;
      color: #cc624e;
      margin: 0;
      font-size: 1.5rem;
    }

    .modal-mensagem .modal-body {
      padding: 25px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    .form-control:focus {
      outline: none;
      border-color: #cc624e;
    }

    textarea.form-control {
      resize: vertical;
      min-height: 120px;
    }

    .modal-actions {
      display: flex;
      gap: 15px;
      justify-content: flex-end;
      margin-top: 25px;
    }

    .btn-cancelar {
      background: #6c757d;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s;
    }

    .btn-cancelar:hover {
      background: #5a6268;
    }

    .btn-enviar {
      background: #cc624e;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.3s;
    }

    .btn-enviar:hover {
      background: #e07b67;
    }

    .btn-enviar:disabled {
      background: #ccc;
      cursor: not-allowed;
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

    /* Botão de mensagem nos cards */
    .btn-mensagem-artista {
      background: #cc624e;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      cursor: pointer;
      font-weight: bold;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .btn-mensagem-artista:hover {
      background: #e07b67;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(204, 98, 78, 0.3);
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

                <!-- Botão de mensagem -->
                <div class="artista-actions">
                  <?php if ($usuarioLogado && $tipoUsuario === "cliente"): ?>
                    <button class="btn-mensagem-artista"
                      onclick="abrirModalMensagem(<?php echo $artista['id']; ?>, '<?php echo htmlspecialchars($artista['nome']); ?>')">
                      <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                  <?php elseif (!$usuarioLogado): ?>
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

  <!-- MODAL DE MENSAGEM -->
  <div id="modalMensagem" class="modal-mensagem">
    <div class="modal-conteudo">
      <div class="modal-header">
        <h2 id="modalTitulo">Enviar Mensagem</h2>
        <button class="btn-fechar" onclick="fecharModalMensagem()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body">
        <form id="formMensagem" method="POST">
          <input type="hidden" id="artista_id" name="artista_id">
          
          <div class="form-group">
            <label for="cliente_nome">Seu Nome</label>
            <input type="text" id="cliente_nome" name="cliente_nome" class="form-control" 
                   value="<?php echo isset($_SESSION['clientes']['nome']) ? htmlspecialchars($_SESSION['clientes']['nome']) : ''; ?>" 
                   required>
          </div>

          <div class="form-group">
            <label for="cliente_email">Seu Email</label>
            <input type="email" id="cliente_email" name="cliente_email" class="form-control"
                   value="<?php echo isset($_SESSION['clientes']['email']) ? htmlspecialchars($_SESSION['clientes']['email']) : ''; ?>" 
                   required>
          </div>

          <div class="form-group">
            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" class="form-control" rows="6" 
                      placeholder="Escreva sua mensagem para o artista..." required></textarea>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancelar" onclick="fecharModalMensagem()">Cancelar</button>
            <button type="submit" class="btn-enviar">
              <i class="fas fa-paper-plane"></i> Enviar Mensagem
            </button>
          </div>
        </form>
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

    // Funções do Modal de Mensagem
    let artistaAtualId = null;
    let artistaAtualNome = null;

    function abrirModalMensagem(artistaId, artistaNome) {
      artistaAtualId = artistaId;
      artistaAtualNome = artistaNome;
      
      const modal = document.getElementById('modalMensagem');
      const titulo = document.getElementById('modalTitulo');
      const artistaIdInput = document.getElementById('artista_id');
      
      titulo.textContent = `Enviar Mensagem para ${artistaNome}`;
      artistaIdInput.value = artistaId;
      
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function fecharModalMensagem() {
      const modal = document.getElementById('modalMensagem');
      modal.classList.remove('active');
      document.body.style.overflow = 'auto';
      
      // Limpar formulário
      document.getElementById('formMensagem').reset();
    }

    // Fechar modal ao clicar fora
    document.getElementById('modalMensagem').addEventListener('click', function(e) {
      if (e.target === this) {
        fecharModalMensagem();
      }
    });

    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        fecharModalMensagem();
      }
    });

    // Envio do formulário - VERSÃO COM DEBUG
document.getElementById('formMensagem').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const btnEnviar = this.querySelector('.btn-enviar');
  
  console.log('=== INICIANDO ENVIO ===');
  console.log('Dados do formulário:');
  for (let pair of formData.entries()) {
    console.log(pair[0] + ': ' + pair[1]);
  }
  
  // Desabilitar botão
  btnEnviar.disabled = true;
  btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
  
  console.log('Fazendo fetch para enviar_mensagem.php...');
  
  fetch('mensagem-para-artista.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    console.log('Status da resposta:', response.status);
    console.log('OK?', response.ok);
    return response.text().then(text => {
      console.log('Resposta bruta:', text);
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error('Erro ao parsear JSON:', e);
        throw new Error('Resposta não é JSON: ' + text);
      }
    });
  })
  .then(data => {
    console.log('Resposta parseada:', data);
    if (data.success) {
      Swal.fire({
        title: 'Sucesso!',
        text: data.message,
        icon: 'success',
        confirmButtonColor: '#cc624e'
      }).then(() => {
        fecharModalMensagem();
      });
    } else {
      Swal.fire({
        title: 'Erro!',
        text: data.message,
        icon: 'error',
        confirmButtonColor: '#cc624e'
      });
    }
  })
  .catch(error => {
    console.error('Erro completo:', error);
    Swal.fire({
      title: 'Erro de Conexão!',
      html: 'Erro ao enviar mensagem:<br>' + error.message,
      icon: 'error',
      confirmButtonColor: '#cc624e'
    });
  })
  .finally(() => {
    // Reabilitar botão
    btnEnviar.disabled = false;
    btnEnviar.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Mensagem';
  });
});
  </script>
</body>
</html>