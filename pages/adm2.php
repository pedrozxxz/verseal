<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admhome2.css">
    <script defer src="../js/admhome.js"></script>
    <title>Clientes - Verseal</title>
</head>
<body>
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php">Início</a>
            <a href="adm2.php" class="active">Clientes</a>
            <a href="adm3.php">Artistas</a>
            <a href="adm4.php">Obras</a>
            <a href="adm5.php">Contato</a>
        </nav>
        <button class="toggle-btn">⟲</button>
        <button class="logout-btn">Sair</button>
    </aside>

    <main class="dashboard">
        <header class="topbar">
            <h1>Clientes</h1>
            <span class="welcome">Gerencie os clientes cadastrados</span>
        </header>

        <section class="content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Compras</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Francisco Oliveira</td>
                            <td>francisco@gmail.com</td>
                            <td>5</td>
                            <td>
                                <button class="edit">Editar</button>
                                <button class="delete">Excluir</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Pablo Picasso</td>
                            <td>picasso@gmail.com</td>
                            <td>6</td>
                            <td>
                                <button class="edit">Editar</button>
                                <button class="delete">Excluir</button>
                            </td>
                        </tr>
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
