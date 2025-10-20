<!DOCTYPE html>
<html lang="pt=br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admhome.css">
    <script defer src="../js/admhome.js"></script>
    <title>Adm5</title>
</head>
<body>
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php" class="active">Início</a>
            <a href="adm2.php">Clientes</a>
            <a href="adm3.php">Artistas</a>
            <a href="adm4.php">Obras</a>
            <a href="adm5.php">Contato</a>
        </nav>
    <header class="topbar">
        <h1>Mensagens</h1>
        <span class="welcome">Veja as mensagens de contato enviadas</span>
    </header>

    <section class="content">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Mensagem</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Maria Silva</td><td>maria@gmail.com</td><td>Gostei muito da galeria!</td><td><button class="delete">Excluir</button></td></tr>
                    <tr><td>João Santos</td><td>joaos@gmail.com</td><td>Tem obras novas?</td><td><button class="delete">Excluir</button></td></tr>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <button class="refresh">Atualizar</button>
            <p class="view-more">Ver Mais</p>
        </div>
    </section>
</main>
</body>
</html>