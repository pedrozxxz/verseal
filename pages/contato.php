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

// Criar tabela de mensagens se não existir
$sql_create_table = "
CREATE TABLE IF NOT EXISTS mensagens_contato (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lida TINYINT(1) DEFAULT 0
)";

if (!$conn->query($sql_create_table)) {
    error_log("Erro ao criar tabela: " . $conn->error);
}

// Verificar se usuário está logado (cliente ou artista)
$usuarioLogado = null;
$tipoUsuario = null;

// Verifica se há sessão de cliente (corrigido para "clientes" no plural)
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
}
// Verifica se há sessão de artista (corrigido para "artistas" no plural)
elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $mensagemContato = trim($_POST['mensagem']);

    if (empty($nome) || empty($email) || empty($mensagemContato)) {
        $mensagem = "<p class='erro'>Por favor, preencha todos os campos.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "<p class='erro'>Digite um e-mail válido.</p>";
    } else {
        // Salvar no banco de dados
        $sql = "INSERT INTO mensagens_contato (nome, email, mensagem) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $email, $mensagemContato);
        
        if ($stmt->execute()) {
            $mensagem = "<p class='sucesso'>Obrigado por entrar em contato, <strong>$nome</strong>! Responderemos em breve.</p>";
        } else {
            $mensagem = "<p class='erro'>Erro ao enviar mensagem. Tente novamente.</p>";
            error_log("Erro ao salvar mensagem: " . $stmt->error);
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contato - Verseal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/contato.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* Estilos para mensagens */
    .sucesso {
      background: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #c3e6cb;
      margin-bottom: 20px;
    }
    
    .erro {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #f5c6cb;
      margin-bottom: 20px;
    }
    
    /* Estilos do formulário */
    .container {
      max-width: 600px;
      margin: 100px auto 40px;
      padding: 40px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .container h1 {
      text-align: center;
      color: #cc624e;
      margin-bottom: 30px;
      font-family: 'Playfair Display', serif;
    }
    
    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    label {
      font-weight: 600;
      color: #333;
    }
    
    input, textarea {
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s;
      font-family: 'Open Sans', sans-serif;
    }
    
    input:focus, textarea:focus {
      outline: none;
      border-color: #cc624e;
    }
    
    textarea {
      height: 150px;
      resize: vertical;
    }
    
    button {
      background: #cc624e;
      color: white;
      border: none;
      padding: 15px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
      font-family: 'Open Sans', sans-serif;
    }
    
    button:hover {
      background: #e07b67;
    }
    
    .voltar {
      text-align: center;
      margin-top: 20px;
    }
    
    .voltar a {
      color: #666;
      text-decoration: none;
      transition: color 0.3s;
    }
    
    .voltar a:hover {
      color: #cc624e;
    }

    /* Informações de contato */
    .info-contato {
      background: #f8f9fa;
      padding: 25px;
      border-radius: 10px;
      margin-bottom: 30px;
      border-left: 4px solid #cc624e;
    }

    .info-contato h3 {
      color: #cc624e;
      margin-bottom: 15px;
      font-family: 'Playfair Display', serif;
    }

    .info-item {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      color: #555;
    }

    .info-item i {
      color: #cc624e;
      width: 20px;
    }

    @media (max-width: 768px) {
      .container {
        margin: 80px 20px 40px;
        padding: 25px;
      }
    }
  </style>
</head>

<body>
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
        <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Ver Perfil</a>
      <?php endif; ?>

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
            <a href="./admhome.php" class="menu-item"><i class="fas fa-user-shield"></i> <span>ADM</span></a>
            <a href="./artistahome.php" class="menu-item"><i class="fas fa-palette"></i> <span>Artista</span></a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <main>
    <div class="container">
      <h1>Fale Conosco</h1>
      
      <div class="info-contato">
        <h3>Informações de Contato</h3>
        <div class="info-item">
          <i class="fas fa-envelope"></i>
          <span>contato@verseal.com</span>
        </div>
        <div class="info-item">
          <i class="fas fa-phone"></i>
          <span>(11) 99999-9999</span>
        </div>
        <div class="info-item">
          <i class="fas fa-clock"></i>
          <span>Segunda a Sexta, 9h às 18h</span>
        </div>
      </div>

      <?php echo $mensagem; ?>
      
      <form method="post" action="">
        <label for="nome">Nome Completo</label>
        <input type="text" name="nome" id="nome" placeholder="Digite seu nome completo" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">

        <label for="email">E-mail</label>
        <input type="email" name="email" id="email" placeholder="Digite seu e-mail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <label for="mensagem">Mensagem</label>
        <textarea name="mensagem" id="mensagem" placeholder="Escreva sua mensagem..." required><?php echo isset($_POST['mensagem']) ? htmlspecialchars($_POST['mensagem']) : ''; ?></textarea>

        <button type="submit">
          <i class="fas fa-paper-plane"></i> Enviar Mensagem
        </button>
      </form>
      
      <p class="voltar"><a href="../index.php">⬅ Voltar ao início</a></p>
    </div>
  </main>

  <script>
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
    });

    // Fade das mensagens
    document.addEventListener('DOMContentLoaded', () => {
      const mensagem = document.querySelector('.sucesso, .erro');
      if (mensagem) {
        setTimeout(() => {
          mensagem.style.transition = 'opacity 0.8s ease';
          mensagem.style.opacity = '0';
          setTimeout(() => {
            if (mensagem.parentNode) {
              mensagem.remove();
            }
          }, 800);
        }, 5000);
      }
    });

    // Manter valores do formulário em caso de erro
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.querySelector('form');
      if (form) {
        // Os valores já são preenchidos pelo PHP
        console.log('Formulário carregado com valores mantidos');
      }
    });
  </script>

</body>

</html>