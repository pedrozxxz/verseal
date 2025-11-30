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

    // CADASTRO DE ARTISTA
    if (isset($_POST["cadastro"])) {
        $nome = $_POST["nome"];
        $nome_artistico = !empty($_POST["nome_artistico"]) ? $_POST["nome_artistico"] : null;
        $insta = !empty($_POST["insta"]) ? $_POST["insta"] : null;
        $email = $_POST["email"];
        $telefone = $_POST["telefone"];
        $cpf = $_POST["cpf"];
        $senha = $_POST["senha"];
        $confirmar = $_POST["confirmar_senha"];

        if ($senha !== $confirmar) {
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
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // INSERE NA TABELA artistas
            $sql = "INSERT INTO artistas (nome, nome_artistico, instagram, email, telefone, cpf, senha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $nome, $nome_artistico, $insta, $email, $telefone, $cpf, $senhaHash);

            if ($stmt->execute()) {
    // Buscar os dados completos do artista recém-cadastrado
    $artista_id = $stmt->insert_id;
    $sql_artista = "SELECT * FROM artistas WHERE id = ?";
    $stmt_artista = $conn->prepare($sql_artista);
    $stmt_artista->bind_param("i", $artista_id);
    $stmt_artista->execute();
    $result_artista = $stmt_artista->get_result();
    $artista_data = $result_artista->fetch_assoc();
    
    // Salvar todas as sessões necessárias
    $_SESSION["artistas"] = $artista_data;
    $_SESSION["artistas_id"] = $artista_data['id'];
    $_SESSION["tipo_usuario"] = 'artista';
    $_SESSION["tipo_artistas"] = 'artista';
    
    // Também criar sessões compatíveis com cliente
    $_SESSION["usuario"] = [
        "id" => $artista_data["id"],
        "nome" => $artista_data["nome"],
        "email" => $artista_data["email"],
        "origem" => "artista"
    ];
    $_SESSION["usuario_id"] = $artista_data["id"];
    $_SESSION["clientes"] = $_SESSION["usuario"];
    $_SESSION["clientes_id"] = $artista_data["id"];
    
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Cadastro realizado!',
                text: 'Você foi cadastrado e logado com sucesso como artista.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'artistahome.php';
            });
        });
    </script>";

            } else {
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Erro!',
                            text: 'Não foi possível criar o cadastro do artista.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                </script>";
            }
        }
    }

    // LOGIN DE ARTISTA
    if (isset($_POST["login"])) {
        $email = $_POST["email"];
        $senha = $_POST["senha"];

        $sql = "SELECT * FROM artistas WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($senha, $row["senha"])) {
                $_SESSION["artista"] = $row["nome"];

                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Login bem sucedido!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'artistahome.php';
                        });
                    });
                </script>";
            } else {
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon:'error', 
                            title:'Senha incorreta!', 
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
                        title:'Artista não encontrado!', 
                        timer:1500, 
                        showConfirmButton:false
                    });
                });
            </script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verseal - Login / Cadastro</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/login.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<div class="hero-bg">
    <!-- SHAPES COLORIDOS -->
    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>

    <!-- QUADROS -->
    <div class="frame frame1"></div>
    <div class="frame frame2"></div>
    <div class="frame frame3"></div>
</div>
 <!-- Adicione isso após as partículas e antes do container -->
<div class="organic-shape shape-1"></div>
<div class="organic-shape shape-2"></div>
<div class="organic-shape shape-3"></div>

  <div class="art-grid"></div>

<div class="curve"></div>
<div class="curve"></div>
<div class="curve"></div>

<img src="../img/monalisa.jfif" class="art-frame frame2">
<img src="../img/vangogh.jfif" class="art-frame frame3">
<img src="../img/moça-bonita.jfif" class="art-frame frame4">
<img src="../img/moço-lowprofile.jfif" class="art-frame frame1">
  <div id="particles-js"></div>

  <main class="container" data-aos="zoom-in">
    <div class="tabs">
      <button id="btn-login" class="tab active">Entrar</button>
      <button id="btn-register" class="tab">Cadastrar</button>
    </div>

    <!-- LOGIN -->
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

    <!-- CADASTRO -->
    <form id="cadastroForm" class="form" method="POST" action="">
      <h2>Cadastrar Artista</h2>
      <div class="campo">
        <input type="text" name="nome" id="cadastroNome" placeholder=" " required />
        <label for="cadastroNome">Nome completo</label>
      </div>
      <div class="campo">
        <input type="text" name="nome_artistico" id="cadastroNomeArtistico" placeholder=" " />
        <label for="cadastroNomeArtistico">Nome artístico (opcional)</label>
      </div>
      <div class="campo">
        <input type="text" name="insta" id="cadastroInsta" placeholder=" " />
        <label for="cadastroInsta">Instagram</label>
      </div>
      <div class="campo">
        <input type="email" name="email" id="cadastroEmail" placeholder=" " required />
        <label for="cadastroEmail">Email</label>
      </div>
      <div class="campo">
        <input type="text" name="telefone" id="cadastroTelefone" placeholder=" " required />
        <label for="cadastroTelefone">Telefone</label>
      </div>
      <div class="campo">
        <input type="text" name="cpf" id="cadastroCPF" placeholder=" " required />
        <label for="cadastroCPF">CPF</label>
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
    </form>

    <p>Não é um artista? <a href="login.php">Cadastre-se como cliente</a></p>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <script src="../js/login.js"></script>
</body>
</html>
