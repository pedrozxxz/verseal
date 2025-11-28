<?php
session_start();

// Configurações do banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se veio do checkout
$fromCheckout = isset($_GET['from']) && $_GET['from'] === 'checkout';

// Determinar URL padrão de voltar
$voltarUrl = '../index.php';
if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === "artista") {
  $voltarUrl = 'artistahome.php';
}
if ($fromCheckout) {
  $voltarUrl = 'checkout.php';
}

// Função auxiliar para escapar JS strings em alertas
function js_escape($s) {
    return str_replace(["\\", "'", "\"", "\n", "\r"], ["\\\\","\\'","\\\"","\\n","\\r"], $s);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // ========================= CADASTRO =========================
  if (isset($_POST["cadastro"])) {
    $nome = trim($_POST["nome"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $senha = $_POST["senha"] ?? '';
    $confirmar = $_POST["confirmar_senha"] ?? '';

    if ($senha !== $confirmar) {
      $msg = "Senhas não coincidem!";
      echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
      echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'error', title:'".js_escape($msg)."', timer:1500, showConfirmButton:false }); });</script>";
    } else {
      // Verificar se e-mail já existe em usuarios ou artistas
      $sql_check = "SELECT 'u' AS tipo, id FROM usuarios WHERE email = ? UNION SELECT 'a' AS tipo, id FROM artistas WHERE email = ?";
      $stmt_check = $conn->prepare($sql_check);
      $stmt_check->bind_param("ss", $email, $email);
      $stmt_check->execute();
      $result_check = $stmt_check->get_result();

      if ($result_check->num_rows > 0) {
        $msg = "Este e-mail já está cadastrado!";
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'error', title:'".js_escape($msg)."', timer:1500, showConfirmButton:false }); });</script>";
      } else {
        // Inserir novo usuário em usuarios
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo, ativo, criado_em)
        VALUES (?, ?, ?, 'usuario', 1, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $email, $senhaHash);

        if ($stmt->execute()) {
          // Buscar dados do novo usuário
          $user_id = $stmt->insert_id;
          $sql_user = "SELECT * FROM usuarios WHERE id = ?";
          $stmt_user = $conn->prepare($sql_user);
          $stmt_user->bind_param("i", $user_id);
          $stmt_user->execute();
          $new_user = $stmt_user->get_result()->fetch_assoc();

          // Salvar sessões compatíveis (cliente)
          $_SESSION["usuario"] = $new_user;
          $_SESSION["usuario_id"] = $new_user['id'];
          $_SESSION["clientes"] = $new_user;
          $_SESSION["clientes_id"] = $new_user['id'];
          $_SESSION["tipo_usuario"] = 'cliente';
          $_SESSION["tipo_clientes"] = 'cliente';

          // Verificar se existe artista com mesmo email (caso queira vincular)
          $sql_artista = "SELECT id, nome, email, imagem_perfil FROM artistas WHERE email = ? AND ativo = 1 LIMIT 1";
          $stmt_artista = $conn->prepare($sql_artista);
          $stmt_artista->bind_param("s", $email);
          $stmt_artista->execute();
          $resArt = $stmt_artista->get_result();
          $isArtista = $resArt->num_rows > 0;

          if ($isArtista) {
            $dadosArtista = $resArt->fetch_assoc();
            // salvar também a sessão de artista com os dados reais da tabela artistas
            $_SESSION["tipo_usuario"] = 'artista';
            $_SESSION["tipo_artistas"] = 'artista';
            $_SESSION["artistas"] = $dadosArtista;
            $_SESSION["artistas_id"] = $dadosArtista['id'];

            $redirectUrl = 'artistahome.php';
            $mensagem = "Bem-vindo à sua galeria, artista $nome!";
          } else {
            $redirectUrl = '../index.php';
            $mensagem = "Cadastro realizado com sucesso, $nome!";
          }

          if ($fromCheckout) {
            $redirectUrl = 'checkout.php';
          }

          echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
          echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'success', title:'Conta criada!', text:'".js_escape($mensagem)."', confirmButtonText:'Entrar'}).then(()=>{ window.location.href='".js_escape($redirectUrl)."'; }); });</script>";
        } else {
          echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
          echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({title:'Erro!', text:'Não foi possível criar o usuário.', icon:'error', confirmButtonText:'OK'}); });</script>";
        }
      }
    }
  }

  // ========================= LOGIN =========================
  if (isset($_POST["login"])) {
    $email = trim($_POST["email"] ?? '');
    $senha = $_POST["senha"] ?? '';

    // 1) Tentar buscar na tabela usuarios
    $sql_u = "SELECT *, 'usuario' AS origem FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1";
    $stmt_u = $conn->prepare($sql_u);
    $stmt_u->bind_param("s", $email);
    $stmt_u->execute();
    $res_u = $stmt_u->get_result();

    $found = false;
    $redirectUrl = '../index.php';
    $mensagem = "Olá!";

    if ($row = $res_u->fetch_assoc()) {
      // Usuário encontrado na tabela usuarios
      if (!empty($row['senha']) && password_verify($senha, $row['senha'])) {
        $found = true;

        // Sessões base para cliente
        $_SESSION["clientes"] = $row;
        $_SESSION["clientes_id"] = $row["id"];
        $_SESSION["usuario"] = $row;
        $_SESSION["usuario_id"] = $row["id"];

        // Verificar se é admin (email fixo ou coluna tipo)
        if ($email === 'admin@verseal.com' || (isset($row['tipo']) && $row['tipo'] === 'admin')) {
          $_SESSION["tipo_usuario"] = 'admin';
          $_SESSION["admin"] = $row;
          $redirectUrl = 'admhome.php';
          $mensagem = "Bem-vindo ao painel administrativo, {$row['nome']}!";
        } else {
          // Verificar se existe artista com mesmo email (tabela artistas independente)
          $sql_art = "SELECT * FROM artistas WHERE email = ? AND ativo = 1 LIMIT 1";
          $stmt_art = $conn->prepare($sql_art);
          $stmt_art->bind_param("s", $email);
          $stmt_art->execute();
          $res_art = $stmt_art->get_result();

          if ($dadosArt = $res_art->fetch_assoc()) {
            // Artista existe: usar dados reais do artista na sessão artistas
            $_SESSION["tipo_usuario"] = 'artista';
            $_SESSION["tipo_artistas"] = 'artista';
            // Guardar os dados do artista (tabela artistas)
            $_SESSION["artistas"] = $dadosArt;
            $_SESSION["artistas_id"] = $dadosArt['id'];

            // Também manter dados do usuario (caso precise)
            $redirectUrl = 'artistahome.php';
            $mensagem = "Bem-vindo de volta, artista {$dadosArt['nome']}!";
          } else {
            // Cliente comum
            $_SESSION["tipo_usuario"] = 'cliente';
            $_SESSION["tipo_clientes"] = 'cliente';
            $redirectUrl = '../index.php';
            $mensagem = "Olá, {$row['nome']}! Explore as novas obras da Verseal.";
          }
        }
      } else {
        // senha incorreta para usuarios
        $msg = "Senha incorreta!";
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'error', title:'".js_escape($msg)."', text:'Verifique seus dados e tente novamente.', timer:2000, showConfirmButton:false }); });</script>";
      }
    } else {
      // 2) Se não achou em usuarios, tentar tabela artistas (login direto como artista)
      $sql_a = "SELECT * FROM artistas WHERE email = ? AND ativo = 1 LIMIT 1";
      $stmt_a = $conn->prepare($sql_a);
      $stmt_a->bind_param("s", $email);
      $stmt_a->execute();
      $res_a = $stmt_a->get_result();

      if ($artrow = $res_a->fetch_assoc()) {
        // Verificar senha baseada na coluna 'senha' de artistas
        if (!empty($artrow['senha']) && password_verify($senha, $artrow['senha'])) {
          $found = true;

          // Salvar sessão como artista (dados reais da tabela artistas)
          $_SESSION["tipo_usuario"] = 'artista';
          $_SESSION["tipo_artistas"] = 'artista';
          $_SESSION["artistas"] = $artrow;
          $_SESSION["artistas_id"] = $artrow['id'];
// GARANTIR QUE O ARTISTA TENHA AS MESMAS SESSÕES DO CLIENTE
// tentar achar usuário com mesmo email
$sql_user_sync = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
$stmt_user_sync = $conn->prepare($sql_user_sync);
$stmt_user_sync->bind_param("s", $email);
$stmt_user_sync->execute();
$res_user_sync = $stmt_user_sync->get_result();

if ($userData = $res_user_sync->fetch_assoc()) {
    // Se existir usuário vinculado, copia as sessões completas
    $_SESSION["usuario"] = $userData;
    $_SESSION["usuario_id"] = $userData['id'];
    $_SESSION["clientes"] = $userData;
    $_SESSION["clientes_id"] = $userData['id'];
} else {
    // Se NÃO existir usuário em 'usuarios', cria uma versão mínima apenas para evitar erro
    $_SESSION["usuario"] = [
        "id" => $artrow["id"],
        "nome" => $artrow["nome"],
        "email" => $artrow["email"],
        "origem" => "artista"
    ];
    $_SESSION["usuario_id"] = $artrow["id"];
    $_SESSION["clientes"] = $_SESSION["usuario"];
    $_SESSION["clientes_id"] = $artrow["id"];
}

          // opcional: sincronizar com 'usuario' se existir um usuário com mesmo email
          $sql_user_by_email = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
          $stmt_user_by_email = $conn->prepare($sql_user_by_email);
          $stmt_user_by_email->bind_param("s", $email);
          $stmt_user_by_email->execute();
          $res_user_by_email = $stmt_user_by_email->get_result();
          if ($userRow = $res_user_by_email->fetch_assoc()) {
            $_SESSION["usuario"] = $userRow;
            $_SESSION["usuario_id"] = $userRow['id'];
            $_SESSION["clientes"] = $userRow;
            $_SESSION["clientes_id"] = $userRow['id'];
          }

          $redirectUrl = 'artistahome.php';
          $mensagem = "Bem-vindo de volta, artista {$artrow['nome']}!";
        } else {
          $msg = "Senha incorreta!";
          echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
          echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'error', title:'".js_escape($msg)."', text:'Verifique seus dados e tente novamente.', timer:2000, showConfirmButton:false }); });</script>";
        }
      } else {
        // Usuário não encontrado em nenhuma tabela
        $msg = "Usuário não encontrado!";
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'error', title:'".js_escape($msg)."', timer:2000, showConfirmButton:false }); });</script>";
      }
    }

    // Se achou e validou senha com sucesso, redirecionar (somente se $found true e $redirectUrl definido)
    if (!empty($found) && !empty($redirectUrl)) {
      if ($fromCheckout) {
        $redirectUrl = 'checkout.php';
      }

      echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
      echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({icon:'success', title:'Login bem-sucedido!', text:'".js_escape($mensagem)."', confirmButtonText:'Continuar'}).then(()=>{ window.location.href='".js_escape($redirectUrl)."'; }); });</script>";
    }
  } // fim login
} // fim POST

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Login / Cadastro</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet" />

  <!-- CSS -->
  <link rel="stylesheet" href="../css/login.css" />

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .btn-voltar {
      position: fixed;
      top: 20px;
      left: 20px;
      background: rgba(255, 255, 255, 0.9);
      color: #cc624e;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      z-index: 1000;
      border: 2px solid #cc624e;
    }
    .btn-voltar:hover { background: #cc624e; color: white; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(204,98,78,0.3); }

    @media (max-width:768px) {
      .btn-voltar { top:15px; left:15px; padding:8px 16px; font-size:0.9rem; }
    }
  </style>
</head>
<body>
  <a href="<?php echo $voltarUrl; ?>" class="btn-voltar">← Voltar</a>

  <main class="container" data-aos="zoom-in">
    <div class="tabs">
      <button id="btn-login" class="tab active">Entrar</button>
      <button id="btn-register" class="tab">Cadastrar</button>
    </div>

    <!-- Formulário de Login -->
    <form id="loginForm" class="form active" method="POST" action="">
      <h2>Entrar</h2>
      <div class="campo">
        <input type="email" name="email" id="loginEmail" placeholder=" " required />
        <label for="loginEmail">Email</label>
      </div>
      <div class="campo">
        <input type="password" name="senha" id="loginSenha" placeholder=" " required />
        <label for="loginSenha">Senha</label>
      </div>
      <button type="submit" name="login" class="botao-estilizado">Entrar</button>
      <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 0.9rem;">
        <small style="color: #666;">Acesso administrativo: admin@verseal.com</small>
      </div>
    </form>

    <!-- Formulário de Cadastro -->
    <form id="cadastroForm" class="form" method="POST" action="">
      <h2>Cadastrar</h2>
      <div class="campo">
        <input type="text" name="nome" id="cadastroNome" placeholder=" " required />
        <label for="cadastroNome">Nome completo</label>
      </div>
      <div class="campo">
        <input type="email" name="email" id="cadastroEmail" placeholder=" " required />
        <label for="cadastroEmail">Email</label>
      </div>
      <div class="campo">
        <input type="password" name="senha" id="cadastroSenha" placeholder=" " required />
        <label for="cadastroSenha">Senha</label>
      </div>
      <div class="campo">
        <input type="password" name="confirmar_senha" id="cadastroSenhaConfirm" placeholder=" " required />
        <label for="cadastroSenhaConfirm">Confirmar senha</label>
      </div>

      <button type="submit" name="cadastro" class="botao-estilizado">Cadastrar</button>

      <p>Você é um artista? <a href="cadastroArtista.php">Cadastre-se aqui</a></p>
    </form>
  </main>

  <script src="../js/login.js"></script>
</body>
</html>
