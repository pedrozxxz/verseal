<?php
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
        $mensagem = "<p class='sucesso'>Obrigado por entrar em contato, <strong>$nome</strong>! Responderemos em breve.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - Verseal</title>
    <link rel="stylesheet" href="../css/contato.css">
</head>
<body>
    

    <div class="container">
        <h1>Fale Conosco</h1>
        <?php echo $mensagem; ?>
        <form method="post" action="">
    <label for="nome">Nome</label>
    <input type="text" name="nome" id="nome" placeholder="Digite seu nome" required>

    <label for="email">E-mail</label>
    <input type="email" name="email" id="email" placeholder="Digite seu e-mail" required>

    <label for="mensagem">Mensagem</label>
    <textarea name="mensagem" id="mensagem" placeholder="Escreva sua mensagem..." required></textarea>

    <button type="submit">Enviar Mensagem</button>
</form>
        <p class="voltar"><a href="../index.php">⬅ Voltar ao início</a></p>
    </div>

    <script>
    document.addEventListener("mousemove", e => {
      let x = (e.clientX / window.innerWidth - 0.5) * 15;
      let y = (e.clientY / window.innerHeight - 0.5) * 15;
      document.querySelectorAll(".layer").forEach((layer, i) => {
        let depth = (i - 1) * 20;
        layer.style.transform = `rotateY(${x}deg) rotateX(${-y}deg) translateZ(${depth}px)`;
      });
    });
    </script>
    <script>
  // Espera o DOM carregar
  document.addEventListener('DOMContentLoaded', () => {
    const mensagem = document.querySelector('.sucesso, .erro');
    if (mensagem) {
      setTimeout(() => {
        mensagem.style.transition = 'opacity 0.8s ease';
        mensagem.style.opacity = '0';
        setTimeout(() => mensagem.remove(), 800);
      }, 4000);
    }
  });
</script>
</body>
</html>