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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CADASTRO
    if (isset($_POST["cadastro"])) {
        $nome = $_POST["nome"];
        $email = $_POST["email"];
        $senha = $_POST["senha"];
        $confirmar = $_POST["confirmar_senha"];

        if ($senha !== $confirmar) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon:'error',
                        title:'As senhas não coincidem!',
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
                // Inserir usuário
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
                        'nome_artistico' => $new_user['nome_artistico'],
                        'telefone' => $new_user['telefone'],
                        'endereco' => $new_user['endereco'],
                        'foto_perfil' => $new_user['foto_perfil'],
                        'insta' => $new_user['insta']
                    ];
                    
                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Cadastro realizado!',
                                text: 'Você foi cadastrado e logado com sucesso.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => { window.location.href = '../index.php'; });
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

        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($senha, $row["senha"])) {
                // Armazenar TODOS os dados do usuário na sessão
                $_SESSION["usuario"] = [
                    'id' => $row['id'],
                    'nome' => $row['nome'],
                    'email' => $row['email'],
                    'nome_artistico' => $row['nome_artistico'],
                    'telefone' => $row['telefone'],
                    'endereco' => $row['endereco'],
                    'foto_perfil' => $row['foto_perfil'],
                    'insta' => $row['insta']
                ];
                
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Login bem sucedido!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                " . ($fromCheckout ? "window.location.href = 'checkout.php';" : "window.location.href = '../index.php';") . "
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
</head>

<body>
  <a href="../index.php" class="btn-voltar">← Voltar</a>

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