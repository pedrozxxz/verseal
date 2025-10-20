<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admhome.css">
    <script defer src="../js/admhome.js"></script>
    <title>Obras - Verseal</title>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php">Início</a>
            <a href="adm2.php">Clientes</a>
            <a href="adm3.php">Artistas</a>
            <a href="adm4.php" class="active">Obras</a>
            <a href="adm5.php">Contato</a>
        </nav>
        <button class="toggle-btn">⟲</button>
        <button class="logout-btn">Sair</button>
    </aside>

    <!-- Dashboard -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Obras</h1>
            <span class="welcome">Gerencie as obras cadastradas</span>
        </header>

        <section class="content">
            <!-- Tabela de obras -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Artista</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Noite Estrelada</td>
                            <td>Vincent Van Gogh</td>
                            <td>R$ 8.000</td>
                            <td>
                                <button class="edit">Editar</button>
                                <button class="delete">Excluir</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Impressão, Sol Nascente</td>
                            <td>Claude Monet</td>
                            <td>R$ 6.500</td>
                            <td>
                                <button class="edit">Editar</button>
                                <button class="delete">Excluir</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Ações -->
            <div class="actions">
                <button class="refresh">Atualizar</button>
                <p class="view-more">Ver Mais</p>
                <button class="new">Nova Obra</button>
            </div>
        </section>
    </main>
</body>
</html>
