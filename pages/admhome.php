<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Verseal</title>
    <script defer src="../js/admhome.js"></script>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php" class="active">Início</a>
            <a href="adm2.php">Clientes</a>
            <a href="adm3.php">Artistas</a>
            <a href="adm4.php">Obras</a>
            <a href="adm5.php">Contato</a>
        </nav>
        <div class="sidebar-footer">
            <button class="logout-btn">Sair</button>
        </div>
    </aside>

    <!-- DASHBOARD -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Painel Administrativo</h1>
            <span class="welcome">BEM-VINDO, ADM!</span>
        </header>

        <section class="stats">
            <div class="card">
                <img class="icon" src="../img/icon_user.png" alt="Ícone de Clientes">
                <h3>100</h3>
                <p>Clientes</p>
            </div>
            <div class="card">
                <img class="icon" src="../img/artistas.png" alt="Ícone de Artistas">
                <h3>245</h3>
                <p>Artistas</p>
            </div>
            <div class="card">
                <img class="icon" src="../img/paleta_tintas.png" alt="Ícone de Obras">
                <h3>360</h3>
                <p>Obras</p>
            </div>
            <div class="card">
                <img class="icon" src="../img/vendas.png" alt="Ícone de Vendas">
                <h3>R$ 12.500</h3>
                <p>Vendas</p>
            </div>
        </section>

        <section class="overview">
    <h2>Visão Geral</h2>
    <p>Bem-vindo ao painel administrativo da Verseal! Aqui você poderá acompanhar métricas e gerenciar o sistema.</p>

    <!-- Gráfico -->
    <div class="chart-container">
        <canvas id="monthlySalesChart"></canvas>
    </div>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('monthlySalesChart').getContext('2d');
const monthlySalesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        datasets: [{
            label: 'Vendas (R$)',
            data: [1200, 1500, 1000, 1800, 2200, 2000, 2400, 2600, 2100, 2300, 2500, 2700],
            backgroundColor: '#db6d56',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `R$ ${context.raw.toLocaleString()}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<style>
.chart-container {
    width: 100%;
    max-width: 900px;
    margin: 20px auto 0 auto;
}
</style>
<style>
/* ===== RESET ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    display: flex;
    height: 100vh;
    background: linear-gradient(135deg, #f8f4f2, #fff9f8);
    color: #333;
    overflow: hidden;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 250px;
    height: 100vh;
    background: linear-gradient(180deg, rgba(219,109,86,0.95), rgba(167,80,62,0.95));
    backdrop-filter: blur(6px);
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 35px 20px;
    box-shadow: 3px 0 15px rgba(0,0,0,0.15);
    border-right: 2px solid rgba(255,255,255,0.15);
}

.stats .card .icon {
    width: 60px;       /* define a largura fixa */
    height: 60px;      /* define a altura fixa */
    object-fit: contain; /* mantém a proporção da imagem sem cortar */
    margin-bottom: 15px;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 2.6rem;
    color: #fdfdfd;
    letter-spacing: 5px;
    font-style: italic;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
    margin-bottom: 40px;
    cursor: default;
    user-select: none;
    transition: all 0.3s ease;
}

.logo:hover {
    color: #fff3f0;
    text-shadow: 3px 3px 10px #db6d56;
}

.menu {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.menu a {
    text-decoration: none;
    color: #fff;
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.menu a:hover,
.menu a.active {
    background: rgba(255,255,255,0.25);
    transform: translateX(4px);
    box-shadow: 0 2px 10px rgba(255,255,255,0.1);
}

.sidebar-footer {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.logout-btn {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    padding: 10px 15px;
    border-radius: 10px;
    cursor: pointer;
    width: 85%;
    transition: all 0.3s ease;
    font-weight: 600;
}

.logout-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.05);
}

/* ===== DASHBOARD ===== */
.dashboard {
    flex: 1;
    padding: 40px 50px;
    overflow-y: auto;
    animation: fadeIn 0.8s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== TOPBAR ===== */
.topbar {
    display: flex;
    flex-direction: column;
    margin-bottom: 30px;
    border-bottom: 2px solid #f0e0de;
    padding-bottom: 10px;
}

.topbar h1 {
    font-size: 2rem;
    color: #db6d56;
    margin-bottom: 6px;
    text-shadow: 1px 1px 4px rgba(219,109,86,0.3);
}

.topbar .welcome {
    font-size: 1rem;
    color: #666;
    font-style: italic;
}

/* ===== STATS CARDS ===== */
.stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 40px;
}

.card {
    background: #fff;
    flex: 1 1 200px;
    min-width: 180px;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: all 0.3s ease;
    cursor: default;
}

.card:hover {
    box-shadow: 0 10px 25px rgba(219,109,86,0.3);
    transform: translateY(-5px);
}

.card .icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.card h3 {
    font-size: 1.8rem;
    color: #db6d56;
    margin-bottom: 5px;
}

.card p {
    font-size: 1rem;
    color: #555;
}

/* ===== OVERVIEW ===== */
.overview {
    background: #fff;
    padding: 25px 30px;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    transition: 0.3s ease;
}

.overview:hover {
    box-shadow: 0 10px 25px rgba(219,109,86,0.25);
}

.overview h2 {
    color: #db6d56;
    margin-bottom: 15px;
}

.overview p {
    font-size: 1rem;
    color: #555;
    line-height: 1.6;
}

/* ===== RESPONSIVIDADE ===== */
@media (max-width: 950px) {
    body {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        flex-direction: row;
        justify-content: space-around;
        padding: 15px;
        height: auto;
    }

    .menu {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
    }

    .menu a {
        margin: 5px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .dashboard {
        padding: 20px;
    }

    .stats {
        flex-direction: column;
        align-items: center;
    }
}
</style>
</body>
</html>
