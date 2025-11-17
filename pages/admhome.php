<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
// requireAdmin();

// Buscar estatísticas do banco de dados
try {
    // Total de clientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'usuario' AND ativo = 1");
    $totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de artistas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM artistas WHERE ativo = 1");
    $totalArtistas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de obras
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1");
    $totalObras = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de vendas FICTÍCIO FIXO
    $stmt = $pdo->query("SELECT COALESCE(SUM(valor_total), 0) as total FROM pedidos WHERE status = 'pago'");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se não houver vendas reais, usa valor fictício fixo
    if ($resultado['total'] <= 0) {
        // Valor fixo baseado no número de obras (sempre o mesmo cálculo)
        $totalVendas = $totalObras * 325; // Valor fixo médio de R$ 325 por obra
    } else {
        $totalVendas = $resultado['total'];
    }

    // Dados FICTÍCIOS FIXOS para o gráfico (vendas mensais)
    // Valores fixos que não mudam
    $vendasMensais = [
        ['mes' => 'Jan', 'valor_total' => 5200],
        ['mes' => 'Fev', 'valor_total' => 4800],
        ['mes' => 'Mar', 'valor_total' => 6100],
        ['mes' => 'Abr', 'valor_total' => 7300],
        ['mes' => 'Mai', 'valor_total' => 8900],
        ['mes' => 'Jun', 'valor_total' => 10200],
        ['mes' => 'Jul', 'valor_total' => 11500],
        ['mes' => 'Ago', 'valor_total' => 10800],
        ['mes' => 'Set', 'valor_total' => 12400],
        ['mes' => 'Out', 'valor_total' => 13200],
        ['mes' => 'Nov', 'valor_total' => 14500],
        ['mes' => 'Dez', 'valor_total' => 15800]
    ];

    // Últimos clientes cadastrados
    $stmt = $pdo->query("SELECT nome, email, criado_em FROM usuarios WHERE tipo = 'usuario' ORDER BY criado_em DESC LIMIT 5");
    $ultimosClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Últimas obras cadastradas
    $stmt = $pdo->query("SELECT nome, artista, preco, data_cadastro FROM produtos WHERE ativo = 1 ORDER BY data_cadastro DESC LIMIT 5");
    $ultimasObras = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Verseal</title>
    <script defer src="../js/admhome.js"></script>
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
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
            background: linear-gradient(180deg, rgba(219, 109, 86, 0.95), rgba(167, 80, 62, 0.95));
            backdrop-filter: blur(6px);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 35px 20px;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.15);
            border-right: 2px solid rgba(255, 255, 255, 0.15);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.6rem;
            color: #fdfdfd;
            letter-spacing: 5px;
            font-style: italic;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            margin-bottom: 40px;
            cursor: default;
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
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(4px);
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
        }

        .sidebar-footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            cursor: pointer;
            width: 85%;
            font-weight: 600;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
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
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            text-shadow: 1px 1px 4px rgba(219, 109, 86, 0.3);
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
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: 0.3s;
            cursor: default;
        }

        .card:hover {
            box-shadow: 0 10px 25px rgba(219, 109, 86, 0.3);
            transform: translateY(-5px);
        }

        .card .icon {
            width: 60px;
            height: 60px;
            object-fit: contain;
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
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
        }

        .overview:hover {
            box-shadow: 0 10px 25px rgba(219, 109, 86, 0.25);
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

        /* ===== CHART ===== */
        .chart-container {
            width: 100%;
            max-width: 900px;
            margin: 20px auto 0 auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
        }

        /* ===== ACTIVITIES ===== */
        .activities {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        .activity-section h3 {
            color: #db6d56;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .activity-list {
            background: #f8f4f2;
            border-radius: 10px;
            padding: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            border-bottom: 1px solid #e0d6d2;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item i {
            color: #db6d56;
            font-size: 1.2rem;
        }

        .activity-info {
            flex: 1;
        }

        .activity-info strong {
            display: block;
            color: #333;
            font-size: 0.9rem;
        }

        .activity-info span {
            display: block;
            color: #666;
            font-size: 0.8rem;
        }

        .activity-info small {
            color: #999;
            font-size: 0.7rem;
        }

        /* ===== HAMBURGUER MENU ===== */
        .hamburger-menu-desktop {
            position: absolute;
            top: 15px;
            right: 30px;
            z-index: 999;
        }

        .hamburger-desktop {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #e07b67, #cc624e);
            color: white;
            padding: 10px 18px;
            border-radius: 40px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(204, 98, 78, 0.4);
            transition: 0.3s;
        }

        .hamburger-desktop:hover {
            background: linear-gradient(135deg, #cc624e, #e07b67);
            transform: translateY(-2px);
        }

        .hamburger-desktop i {
            font-size: 1.1rem;
        }

        #menu-toggle-desktop {
            display: none;
        }

        .menu-content-desktop {
            display: none;
            position: absolute;
            top: 60px;
            right: 0;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 15px 20px;
            width: 180px;
        }

        #menu-toggle-desktop:checked+.hamburger-desktop+.menu-content-desktop {
            display: block;
        }

        .menu-content-desktop .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #5b4a42;
            padding: 8px 0;
            text-decoration: none;
            transition: 0.3s;
        }

        .menu-content-desktop .menu-item:hover {
            color: #cc624e;
            font-weight: 600;
        }

        .menu-item.active {
            color: #cc624e;
            font-weight: bold;
        }

        .menu-section {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ===== RESPONSIVIDADE ===== */
        @media (max-width:950px) {
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

            .activities {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php" class="active">Início</a>
            <a href="adm-cliente.php">Clientes</a>
            <a href="adm-artista.php">Artistas</a>
            <a href="adm-obras.php">Obras</a>
            <a href="adm-contato.php">Contato</a>
        </nav>
    </aside>

    <!-- Menu Hamburguer Flutuante -->
    <div class="hamburger-menu-desktop">
        <input type="checkbox" id="menu-toggle-desktop">
        <label for="menu-toggle-desktop" class="hamburger-desktop">
            <i class="fas fa-bars"></i>
            <span>ACESSO</span>
        </label>
        <div class="menu-content-desktop">
            <div class="menu-section">
                <a href="../index.php" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Cliente</span>
                </a>
                <a href="./admhome.php" class="menu-item active">
                    <i class="fas fa-user-shield"></i>
                    <span>ADM</span>
                </a>
                <a href="./artistahome.php" class="menu-item">
                    <i class="fas fa-palette"></i>
                    <span>Artista</span>
                </a>
            </div>
        </div>
    </div>

    <!-- DASHBOARD -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Painel Administrativo</h1>
            <span class="welcome">BEM-VINDO, <?php echo strtoupper($_SESSION["usuario"]['nome'] ?? 'ADMIN'); ?>!</span>
        </header>

        <section class="stats">
            <div class="card">
                <img class="icon" src="../img/icon_user.png" alt="Ícone de Clientes">
                <h3><?php echo $totalClientes; ?></h3>
                <p>Clientes</p>
            </div>
            <div class="card">
                <img class="icon" src="../img/artistas.png" alt="Ícone de Artistas">
                <h3><?php echo $totalArtistas; ?></h3>
                <p>Artistas</p>
            </div>
            <div class="card">
                <img class="icon" src="../img/paleta_tintas.png" alt="Ícone de Obras">
                <h3><?php echo $totalObras; ?></h3>
                <p>Obras</p>
            </div>
            <div class="card">
                <img class="icon" src="../img/vendas.png" alt="Ícone de Vendas">
                <h3>R$ <?php echo number_format($totalVendas, 2, ',', '.'); ?></h3>
                <p>Vendas Totais</p>
            </div>
        </section>

        <section class="overview">
            <h2>Visão Geral</h2>
            <p>Bem-vindo ao painel administrativo da Verseal! Aqui você poderá acompanhar métricas e gerenciar o sistema. O gráfico abaixo mostra o desempenho das vendas mensais.</p>

            <!-- Gráfico -->
            <div class="chart-container">
                <canvas id="monthlySalesChart"></canvas>
            </div>

            <!-- Últimas Atividades -->
            <div class="activities">
                <div class="activity-section">
                    <h3>Últimos Clientes</h3>
                    <div class="activity-list">
                        <?php if (!empty($ultimosClientes)): ?>
                            <?php foreach ($ultimosClientes as $cliente): ?>
                                <div class="activity-item">
                                    <i class="fas fa-user-plus"></i>
                                    <div class="activity-info">
                                        <strong><?php echo htmlspecialchars($cliente['nome']); ?></strong>
                                        <span><?php echo htmlspecialchars($cliente['email']); ?></span>
                                        <small><?php echo date('d/m/Y', strtotime($cliente['criado_em'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <i class="fas fa-info-circle"></i>
                                <div class="activity-info">
                                    <strong>Nenhum cliente cadastrado</strong>
                                    <span>Aguarde novos cadastros</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="activity-section">
                    <h3>Últimas Obras</h3>
                    <div class="activity-list">
                        <?php if (!empty($ultimasObras)): ?>
                            <?php foreach ($ultimasObras as $obra): ?>
                                <div class="activity-item">
                                    <i class="fas fa-palette"></i>
                                    <div class="activity-info">
                                        <strong><?php echo htmlspecialchars($obra['nome']); ?></strong>
                                        <span><?php echo htmlspecialchars($obra['artista']); ?></span>
                                        <small>R$ <?php echo number_format($obra['preco'], 2, ',', '.'); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <i class="fas fa-info-circle"></i>
                                <div class="activity-info">
                                    <strong>Nenhuma obra cadastrada</strong>
                                    <span>Cadastre novas obras</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlySalesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php echo '"' . implode('","', array_column($vendasMensais, 'mes')) . '"'; ?>],
                datasets: [{
                    label: 'Vendas Mensais (R$)',
                    data: [<?php echo implode(',', array_column($vendasMensais, 'valor_total')); ?>],
                    backgroundColor: 'rgba(219, 109, 86, 0.1)',
                    borderColor: '#db6d56',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#db6d56',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: "'Poppins', sans-serif"
                            },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            family: "'Poppins', sans-serif"
                        },
                        bodyFont: {
                            family: "'Poppins', sans-serif"
                        },
                        callbacks: {
                            label: function (context) { 
                                return `R$ ${context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`; 
                            }
                        }
                    }
                },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            },
                            font: {
                                family: "'Poppins', sans-serif"
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: "'Poppins', sans-serif"
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });
    </script>

    <!-- JS Hamburguinho -->
    <script>
        document.addEventListener('click', function (e) {
            const toggle = document.getElementById('menu-toggle-desktop');
            if (!e.target.closest('.hamburger-menu-desktop')) {
                toggle.checked = false;
            }
        });
    </script>
</body>

</html>