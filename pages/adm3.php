<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admhome.css">
    <script defer src="../js/admhome.js"></script>
    <title>ADM3</title>
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
        <h1>Artistas</h1>
        <span class="welcome">Gerencie os artistas cadastrados</span>
    </header>

    <section class="content">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Estilo</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Vincent Van Gogh</td><td>Pós-impressionismo</td><td>vangogh@gmail.com</td><td><button class="edit">Editar</button><button class="delete">Excluir</button></td></tr>
                    <tr><td>Claude Monet</td><td>Impressionismo</td><td>monet@gmail.com</td><td><button class="edit">Editar</button><button class="delete">Excluir</button></td></tr>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <button class="refresh">Atualizar</button>
            <p class="view-more">Ver Mais</p>
            <button class="new">Novo Cadastro</button>
        </div>
    </section>
</main>

</body>
</html>