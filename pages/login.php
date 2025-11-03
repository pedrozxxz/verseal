<?php
session_start();

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

// Verificar se o usuário já está logado para determinar para onde voltar
$isArtista = false;
if (isset($_SESSION["usuario"])) {
    if (is_array($_SESSION["usuario"])) {
        // Verificar se é artista procurando na tabela de artistas
        $nomeUsuario = $_SESSION["usuario"]['nome'];
        $sql_artista = "SELECT id FROM artistas WHERE nome = ? AND ativo = 1";
        $stmt_artista = $conn->prepare($sql_artista);
        $stmt_artista->bind_param("s", $nomeUsuario);
        $stmt_artista->execute();
        $result_artista = $stmt_artista->get_result();
        $isArtista = $result_artista->num_rows > 0;
    }
}

// Determinar URL de voltar
$voltarUrl = '../index.php'; // padrão para cliente
if ($isArtista) {
    $voltarUrl = 'artistahome.php';
}
// Se veio do checkout, prioriza o checkout
if ($fromCheckout) {
    $voltarUrl = 'checkout.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CADASTRO
    if (isset($_POST["cadastro"])) {
        $nome = $_POST["nome"];
        $email = $_POST["email"];
        $senha = $_POST["senha"];
        $confirmar = $_POST["confirmar_senha"];

        if ($senha !== $confirmar) {
            // SweetAlert de senha não coincidente
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon:'error',
                        title:'Senhas não coincidem!',
                        timer:1500,
                        showConfirmButton:false
                    });
                });
            </script>";
        } else {
            // Verificar se e-mail já existe
            $sql_check = "SELECT id FROM usuarios WHERE email = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // E-mail já existe
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon:'error',
                            title:'Este e-mail já está cadastrado!',
                            timer:1500,
                            showConfirmButton:false
                        });
                    });
                </script>";
            } else {
                // Inserir usuário (sem a coluna tipo)
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $nome, $email, $senhaHash);

                if ($stmt->execute()) {
                    // Buscar o usuário recém-criado para obter o ID
                    $user_id = $stmt->insert_id;
                    $sql_user = "SELECT * FROM usuarios WHERE id = ?";
                    $stmt_user = $conn->prepare($sql_user);
                    $stmt_user->bind_param("i", $user_id);
                    $stmt_user->execute();
                    $result_user = $stmt_user->get_result();
                    $new_user = $result_user->fetch_assoc();
                    
                    // Armazenar TODOS os dados do usuário na sessão
                    $_SESSION["usuario"] = [
                        'id' => $new_user['id'],
                        'nome' => $new_user['nome'],
                        'email' => $new_user['email'],
                        'nome_artistico' => $new_user['nome_artistico'] ?? null,
                        'telefone' => $new_user['telefone'] ?? null,
                        'endereco' => $new_user['endereco'] ?? null,
                        'foto_perfil' => $new_user['foto_perfil'] ?? null,
                        'insta' => $new_user['insta'] ?? null
                    ];
                    $_SESSION["usuario_id"] = $new_user['id'];
                    $_SESSION["tipo_usuario"] = 'usuario'; // Definir como usuário padrão
                    
                    // Verificar se é artista após cadastro
                    $nomeUsuario = $new_user['nome'];
                    $sql_artista = "SELECT id FROM artistas WHERE nome = ? AND ativo = 1";
                    $stmt_artista = $conn->prepare($sql_artista);
                    $stmt_artista->bind_param("s", $nomeUsuario);
                    $stmt_artista->execute();
                    $result_artista = $stmt_artista->get_result();
                    $isArtista = $result_artista->num_rows > 0;
                    
                    $redirectUrl = $fromCheckout ? 'checkout.php' : ($isArtista ? 'artistahome.php' : '../index.php');
                    
                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Cadastro realizado!',
                                text: 'Você foi cadastrado e logado com sucesso.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => { 
                                window.location.href = '$redirectUrl';
                            });
                        });
                    </script>";
                } else {
                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Não foi possível criar o usuário.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                    </script>";
                }
            }
        }
    }

    // LOGIN
    if (isset($_POST["login"])) {
        $email = $_POST["email"];
        $senha = $_POST["senha"];

        // Buscar usuário (sem a coluna tipo)
        $sql = "SELECT id, nome, email, senha, nome_artistico, telefone, endereco, foto_perfil, insta FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($senha, $row["senha"])) {
                // Login bem sucedido - salvar dados na sessão
                $_SESSION["usuario"] = [
                    'id' => $row['id'],
                    'nome' => $row['nome'],
                    'email' => $row['email'],
                    'nome_artistico' => $row['nome_artistico'] ?? null,
                    'telefone' => $row['telefone'] ?? null,
                    'endereco' => $row['endereco'] ?? null,
                    'foto_perfil' => $row['foto_perfil'] ?? null,
                    'insta' => $row['insta'] ?? null
                ];
                $_SESSION["usuario_id"] = $row["id"];
                $_SESSION["tipo_usuario"] = 'usuario'; // Definir como usuário padrão
                
                // Verificar se é artista
                $nomeUsuario = $row['nome'];
                $sql_artista = "SELECT id FROM artistas WHERE nome = ? AND ativo = 1";
                $stmt_artista = $conn->prepare($sql_artista);
                $stmt_artista->bind_param("s", $nomeUsuario);
                $stmt_artista->execute();
                $result_artista = $stmt_artista->get_result();
                $isArtista = $result_artista->num_rows > 0;
                
                $redirectUrl = $fromCheckout ? 'checkout.php' : ($isArtista ? 'artistahome.php' : '../index.php');
                
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Login bem sucedido!',
                            text: 'Bem-vindo, " . $row["nome"] . "!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '$redirectUrl';
                        });
                    });
                </script>";
            } else {
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon:'error', 
                            title:'Você digitou senha ou e-mail incorreto, verifique!', 
                            timer:1500, 
                            showConfirmButton:false
                        });
                    });
                </script>";
            }
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon:'error', 
                        title:'Usuário não encontrado!', 
                        timer:1500, 
                        showConfirmButton:false
                    });
                });
            </script>";
        }
    }
} // Fecha o if ($_SERVER["REQUEST_METHOD"] == "POST")

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Login / Cadastro</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap"
    rel="stylesheet" />

  <!-- CSS -->
  <link rel="stylesheet" href="../css/login.css" />

  <!-- SweetAlert2 CSS -->
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

    .btn-voltar:hover {
      background: #cc624e;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(204, 98, 78, 0.3);
    }

    @media (max-width: 768px) {
      .btn-voltar {
        top: 15px;
        left: 15px;
        padding: 8px 16px;
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body>
  <!-- Botão Voltar Inteligente -->
  <a href="<?php echo $voltarUrl; ?>" class="btn-voltar">← Voltar</a>

  <!-- Partículas no fundo -->
  <div id="particles-js"></div>

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
      
      <!-- Acesso Admin (opcional) -->
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

    <div id="particles-js"></div>
  </main>

  <!-- Particles.js -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

  <!-- Script login -->
  <script src="../js/login.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>