<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Verseal</title>
    <link rel="stylesheet" href="../css/admhome.css">
    <script defer src="../js/admhome.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php" class="active">Início</a>
            <a href="adm2.php">Clientes</a>
            <a href="adm3.php">Artistas</a>
            <a href="adm4.php">Obras</a>
            <a href="adm5.php">Contato</a>
        </nav>
        <button class="toggle-btn">⟲</button>
        <button class="logout-btn">Sair</button>
    </aside>

    <!-- Dashboard -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Painel Administrativo</h1>
            <span class="welcome">BEM-VINDO, ADM!</span>
        </header>

        <section class="stats">
            <div class="card">
                <i class="icon"></i>
                <h3>100</h3>
                <p>Clientes</p>
            </div>
            <div class="card">
                <i class="icon"></i>
                <h3>245</h3>
                <p>Artistas</p>
            </div>
            <div class="card">
                <i class="icon"></i>
                <h3>360</h3>
                <p>Obras</p>
            </div>
            <div class="card">
                <i class="icon"></i>
                <h3>R$ 12.500</h3>
                <p>Vendas</p>
            </div>
        </section>

        <section class="overview">
            <h2>Visão Geral</h2>
            <p>Bem-vindo ao painel administrativo da Verseal! Aqui você poderá futuramente acompanhar as métricas e gerenciar o sistema.</p>
        </section>
    </main>
</body>
</html>
