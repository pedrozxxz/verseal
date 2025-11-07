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

if (isset($_SESSION["cliente"])) {
    $usuarioLogado = $_SESSION["cliente"];
    $tipoUsuario = "cliente";
} elseif (isset($_SESSION["artista"])) {
    $usuarioLogado = $_SESSION["artista"];
    $tipoUsuario = "artista";
}

// Buscar artistas ativos do banco de dados
$sql_artistas = "SELECT * FROM artistas WHERE ativo = 1 ORDER BY nome ASC";
$result_artistas = $conn->query($sql_artistas);
$artistas = [];

if ($result_artistas && $result_artistas->num_rows > 0) {
    while ($artista = $result_artistas->fetch_assoc()) {
        $artistas[] = [
            'id' => $artista['id'],
            'nome' => $artista['nome'],
            'idade' => $artista['idade'] ?? '',
            'descricao' => $artista['descricao'] ?? '',
            'biografia' => $artista['biografia'] ?? '', // agora pega do banco corretamente
            'telefone' => $artista['telefone'] ?? '',
            'email' => $artista['email'] ?? '',
            'instagram' => $artista['instagram'] ?? '',
            'cor_gradiente' => $artista['cor_gradiente'] ?? 'linear-gradient(135deg, #e07b67, #cc624e)',
            'icone' => $artista['icone'] ?? 'fas fa-paint-brush',
            'foto_perfil' => $artista['foto_perfil'] ?? ''
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
  </style>
</head>

<body>
  <!-- HEADER -->
  <header>
    <div class="logo">Verseal</div>
    <nav>
      <a href="../index.php">Início</a>
      <a href="../pages/produto.php">Obras</a>
      <a href="../pages/sobre.php">Sobre</a>
      <a href="../pages/artistas.php">Artistas</a>
      <a href="../pages/contato.php">Contato</a>
      
      <a href="./pages/carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
      
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
                    echo htmlspecialchars($usuarioLogado['nome']);
                  } elseif ($tipoUsuario === "artista") {
                    echo htmlspecialchars($usuarioLogado['nome_artistico']);
                  }
                  ?>
                </span>!
              </p>
            </div>
            <div class="dropdown-divider"></div>

            <?php if ($tipoUsuario === "cliente"): ?>
              <a href="../pages/perfilCliente.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
              <a href="../pages/favoritos.php" class="dropdown-item"><i class="fas fa-heart"></i> Favoritos</a>
            <?php endif; ?>

            <div class="dropdown-divider"></div>
            <a href="../pages/logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>

          <?php else: ?>
            <div class="user-info"><p>Faça login para acessar seu perfil</p></div>
            <div class="dropdown-divider"></div>
            <a href="../pages/login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
            <a href="../pages/login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Menu Hamburguer Flutuante -->
      <div class="hamburger-menu-desktop">
        <input type="checkbox" id="menu-toggle-desktop">
        <label for="menu-toggle-desktop" class="hamburger-desktop">
          <i class="fas fa-bars"></i>
          <span>ACESSO</span>
        </label>
        <div class="menu-content-desktop">
          <div class="menu-section">
            <a href="../index.php" class="menu-item" onclick="document.getElementById('menu-toggle-desktop').checked = false;">
              <i class="fas fa-user"></i> <span>Cliente</span>
            </a>
            <a href="../pages/admhome.php" class="menu-item"><i class="fas fa-user-shield"></i> <span>ADM</span></a>
            <a href="../pages/artistahome.php" class="menu-item"><i class="fas fa-palette"></i> <span>Artista</span></a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- HERO ARTISTAS -->
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
  <section class="artistas" id="artistas">
    <h2>Conheça Nossos Talentos</h2>
    <p class="artistas-subtitle">
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
          <div class="card-artista fade-in">
            <div class="artista-imagem" style="background: <?php echo $artista['cor_gradiente']; ?>;">
              <?php if (!empty($artista['foto_perfil'])): ?>
                <img src="<?php echo $artista['foto_perfil']; ?>" 
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
                <p class="artista-idade"><?php echo $artista['idade']; ?> anos</p>
              <?php endif; ?>
              
              <!-- Descrição curta -->
              <p class="artista-descricao">
                <?php echo htmlspecialchars($artista['descricao']); ?>
              </p>

              <!-- Biografia completa -->
              <?php if (!empty($artista['biografia'])): ?>
                <div class="artista-biografia">
                  <div class="biografia-titulo">
                    <i class="fas fa-book-open"></i>
                    <span>Biografia</span>
                  </div>
                  <p class="biografia-texto">
                    <?php echo htmlspecialchars($artista['biografia']); ?>
                  </p>
                </div>
              <?php endif; ?>

              <div class="artista-contatos">
                <?php if (!empty($artista['telefone'])): ?>
                  <div class="contato-item">
                    <i class="fas fa-phone"></i>
                    <span>Telefone: <?php echo htmlspecialchars($artista['telefone']); ?></span>
                  </div>
                <?php endif; ?>

                <?php if (!empty($artista['email'])): ?>
                  <div class="contato-item">
                    <i class="fas fa-envelope"></i>
                    <span>Email: <?php echo htmlspecialchars($artista['email']); ?></span>
                  </div>
                <?php endif; ?>

                <?php if (!empty($artista['instagram'])): ?>
                  <div class="contato-item">
                    <i class="fab fa-instagram"></i>
                    <span>Instagram: <?php echo htmlspecialchars($artista['instagram']); ?></span>
                  </div>
                <?php endif; ?>
              </div>

              <div class="artista-actions">
                <?php if ($usuarioLogado && is_array($usuarioLogado) && ($usuarioLogado['nome'] === $artista['nome'])): ?>
                
                <?php elseif ($usuarioLogado): ?>
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
  </section>

  <!-- MODAL ENVIAR MENSAGEM -->
  <div id="modalMensagem" class="modal-mensagem">
    <div class="modal-conteudo-mensagem">
      <div class="modal-header-mensagem">
        <h2 id="modalTituloMensagem">Enviar Mensagem</h2>
        <button class="btn-fechar-modal" onclick="fecharModalMensagem()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body-mensagem">
        <form id="formMensagem" class="form-mensagem" onsubmit="enviarMensagem(event)">
          <input type="hidden" id="artistaId" name="artista_id">
          <input type="hidden" id="artistaNome" name="artista_nome">
          
          <div>
            <label for="assuntoMensagem">Assunto:</label>
            <input type="text" id="assuntoMensagem" name="assunto" placeholder="Assunto da mensagem" required>
          </div>
          
          <div>
            <label for="mensagemTexto">Mensagem:</label>
            <textarea id="mensagemTexto" name="mensagem" placeholder="Escreva sua mensagem para o artista..." required></textarea>
          </div>
          
          <button type="submit" class="btn-enviar-mensagem" id="btnEnviarMensagem">
            <i class="fas fa-paper-plane"></i> Enviar Mensagem
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    let artistaAtual = null;

    function abrirModalMensagem(artistaId, artistaNome) {
      console.log('Abrindo modal para:', artistaNome, 'ID:', artistaId);
      artistaAtual = { id: artistaId, nome: artistaNome };
      document.getElementById('artistaId').value = artistaId;
      document.getElementById('artistaNome').value = artistaNome;
      document.getElementById('modalTituloMensagem').textContent = `Enviar mensagem para ${artistaNome}`;
      document.getElementById('modalMensagem').classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function fecharModalMensagem() {
      console.log('Fechando modal');
      document.getElementById('modalMensagem').classList.remove('active');
      document.body.style.overflow = 'auto';
      document.getElementById('formMensagem').reset();
      artistaAtual = null;
    }

    async function enviarMensagem(event) {
      event.preventDefault();
      console.log('Enviando mensagem...');
      
      const form = event.target;
      const formData = new FormData(form);
      const btnEnviar = document.getElementById('btnEnviarMensagem');
      
      // Desabilitar botão
      btnEnviar.disabled = true;
      btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
      
      try {
        const response = await fetch('enviar_mensagem.php', {
          method: 'POST',
          body: formData
        });
        
        const data = await response.json();
        console.log('Resposta do servidor:', data);
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Mensagem enviada!',
            text: 'Sua mensagem foi enviada com sucesso para o artista.',
            confirmButtonColor: '#28a745'
          });
          fecharModalMensagem();
        } else {
          throw new Error(data.message || 'Erro ao enviar mensagem');
        }
      } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
          icon: 'error',
          title: 'Erro',
          text: error.message || 'Erro ao enviar mensagem. Tente novamente.',
          confirmButtonColor: '#dc3545'
        });
      } finally {
        // Reabilitar botão
        btnEnviar.disabled = false;
        btnEnviar.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Mensagem';
      }
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

    // Dropdown do perfil
    document.addEventListener('DOMContentLoaded', function () {
      const profileIcon = document.getElementById('profile-icon');
      const profileDropdown = document.getElementById('profile-dropdown');
      if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
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

      // Debug: verificar se elementos do modal existem
      console.log('Modal elemento:', document.getElementById('modalMensagem'));
      console.log('Form elemento:', document.getElementById('formMensagem'));
    });
  </script>
</body>
</html>