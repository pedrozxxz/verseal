<?php
session_start();
require_once '../config/database.php';

// Verificar se é admin
if (!isset($_SESSION["tipo_usuario"]) || $_SESSION["tipo_usuario"] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Lista de palavras ofensivas (pode ser expandida conforme necessário)
$palavras_ofensivas = [
    'idiota', 'burro', 'estúpido', 'imbecil', 'retardado', 'animal', 'corno', 'viado',
    'puta', 'prostituta', 'vadia', 'piranha', 'vagabunda', 'merda', 'porra', 'caralho',
    'foda-se', 'foder', 'cu', 'buceta', 'pau', 'rola', 'piroca', 'bosta', 'cacete',
    'arrombado', 'filho da puta', 'desgraçado', 'maldito', 'otário', 'trouxa', 'palhaço'
];

// Processar ações (excluir mensagem)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PROCESSAR EXCLUSÃO DE MENSAGEM
    if (isset($_POST['excluir_mensagem'])) {
        $mensagem_id = $_POST['mensagem_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM mensagens_contato WHERE id = ?");
            $stmt->execute([$mensagem_id]);

            header("Location: adm-contato.php?excluido=1");
            exit;

        } catch (PDOException $e) {
            header("Location: adm-contato.php?erro_exclusao=1");
            exit;
        }
    }

    // PROCESSAR MARCAR COMO LIDA/NÃO LIDA
    if (isset($_POST['marcar_lida'])) {
        $mensagem_id = $_POST['mensagem_id'];
        $lida = $_POST['lida'];

        try {
            $stmt = $pdo->prepare("UPDATE mensagens_contato SET lida = ? WHERE id = ?");
            $stmt->execute([$lida, $mensagem_id]);

            header("Location: adm-contato.php?atualizado=1");
            exit;

        } catch (PDOException $e) {
            header("Location: adm-contato.php?erro_atualizacao=1");
            exit;
        }
    }
}

// Buscar mensagens do banco
try {
    $stmt = $pdo->prepare("
        SELECT * FROM mensagens_contato 
        ORDER BY data_envio DESC
    ");
    $stmt->execute();
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro na consulta de mensagens: " . $e->getMessage());
    $mensagens = [];
}

// Função para verificar se a mensagem contém conteúdo ofensivo
function verificarConteudoOfensivo($mensagem, $palavras_ofensivas) {
    $mensagem_lower = mb_strtolower($mensagem, 'UTF-8');
    
    foreach ($palavras_ofensivas as $palavra) {
        if (strpos($mensagem_lower, mb_strtolower($palavra, 'UTF-8')) !== false) {
            return true;
        }
    }
    return false;
}

// Função para filtrar conteúdo ofensivo
function filtrarConteudoOfensivo($mensagem, $palavras_ofensivas) {
    $mensagem_filtrada = $mensagem;
    
    foreach ($palavras_ofensivas as $palavra) {
        $regex = '/\b' . preg_quote($palavra, '/') . '\b/i';
        $mensagem_filtrada = preg_replace($regex, '***', $mensagem_filtrada);
    }
    
    return $mensagem_filtrada;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens - Verseal</title>
    <script defer src="../js/admhome.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <!-- BARRA LATERAL -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php">Início</a>
            <a href="adm-cliente.php">Clientes</a>
            <a href="adm-artista.php">Artistas</a>
            <a href="adm-obras.php">Obras</a>
            <a href="adm-contato.php" class="active">Contato</a>
        </nav>
    </aside>

    <!-- MENU HAMBÚRGUER FLUTUANTE -->
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

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Mensagens de Contato</h1>
            <span class="welcome">
                Total: <?php echo count($mensagens); ?> mensagens | 
                Não lidas: <?php echo count(array_filter($mensagens, fn($msg) => !$msg['lida'])); ?>
            </span>
        </header>

        <section class="content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user icon"></i> Nome</th>
                            <th><i class="fas fa-envelope icon"></i> Email</th>
                            <th><i class="fas fa-comment icon"></i> Mensagem</th>
                            <th><i class="fas fa-calendar icon"></i> Data</th>
                            <th><i class="fas fa-eye icon"></i> Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mensagens)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">
                                    Nenhuma mensagem recebida
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mensagens as $mensagem): 
                                $conteudo_ofensivo = verificarConteudoOfensivo($mensagem['mensagem'], $palavras_ofensivas);
                                $mensagem_filtrada = filtrarConteudoOfensivo($mensagem['mensagem'], $palavras_ofensivas);
                            ?>
                                <tr class="<?php echo $mensagem['lida'] ? 'mensagem-lida' : 'mensagem-nao-lida'; ?> <?php echo $conteudo_ofensivo ? 'conteudo-ofensivo' : ''; ?>">
                                    <td><?php echo htmlspecialchars($mensagem['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($mensagem['email']); ?></td>
                                    <td class="mensagem-conteudo">
                                        <div class="mensagem-texto">
                                            <?php echo nl2br(htmlspecialchars($mensagem_filtrada)); ?>
                                        </div>
                                        <?php if ($conteudo_ofensivo): ?>
                                            <div class="aviso-ofensivo">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span>Conteúdo ofensivo detectado</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($mensagem['data_envio'])); ?>
                                    </td>
                                    <td>
                                        <span class="status-mensagem <?php echo $mensagem['lida'] ? 'lida' : 'nao-lida'; ?>">
                                            <?php echo $mensagem['lida'] ? 'Lida' : 'Não lida'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="acoes-botoes">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="mensagem_id" value="<?php echo $mensagem['id']; ?>">
                                                <input type="hidden" name="lida" value="<?php echo $mensagem['lida'] ? '0' : '1'; ?>">
                                                <button type="submit" name="marcar_lida" class="btn-status">
                                                    <i class="fas fa-<?php echo $mensagem['lida'] ? 'envelope' : 'envelope-open'; ?>"></i>
                                                    <?php echo $mensagem['lida'] ? 'Marcar como não lida' : 'Marcar como lida'; ?>
                                                </button>
                                            </form>
                                            <button class="delete" onclick="excluirMensagem(<?php echo $mensagem['id']; ?>, '<?php echo htmlspecialchars(addslashes($mensagem['nome'])); ?>')">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="actions">
                <button class="refresh" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Atualizar
                </button>
            </div>
        </section>
    </main>

    <!-- FORMULÁRIO INVISÍVEL PARA EXCLUSÃO -->
    <form id="formExcluir" method="POST" style="display: none;">
        <input type="hidden" id="mensagemExcluirId" name="mensagem_id">
        <input type="hidden" name="excluir_mensagem" value="1">
    </form>

    <script>
        // Função para excluir mensagem
        function excluirMensagem(id, nome) {
            Swal.fire({
                title: 'Excluir Mensagem',
                html: `Tem certeza que deseja excluir a mensagem de <strong>"${nome}"</strong>?<br><small>Esta ação não pode ser desfeita.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                background: '#fff',
                iconColor: '#e74c3c'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('mensagemExcluirId').value = id;
                    document.getElementById('formExcluir').submit();
                }
            });

            return false;
        }

        // Menu hamburguer
        document.addEventListener('click', function (e) {
            const toggle = document.getElementById('menu-toggle-desktop');
            if (!e.target.closest('.hamburger-menu-desktop')) {
                toggle.checked = false;
            }
        });

        // SweetAlert para ações
        <?php if (isset($_GET['excluido']) && $_GET['excluido'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Mensagem excluída!',
                text: 'A mensagem foi excluída com sucesso.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['atualizado']) && $_GET['atualizado'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Status atualizado!',
                text: 'O status da mensagem foi atualizado.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['erro_exclusao']) && $_GET['erro_exclusao'] == 1): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Ocorreu um erro ao excluir a mensagem.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['erro_atualizacao']) && $_GET['erro_atualizacao'] == 1): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Ocorreu um erro ao atualizar o status da mensagem.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>
    </script>

    <style>
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
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(4px);
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
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
            margin-bottom: 25px;
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

        /* ===== TABELA ===== */
        .table-container {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            width: 90%;
            max-width: 1200px;
            margin: 0 auto 45px auto;
            overflow-x: auto;
            transition: 0.3s ease;
        }

        .table-container:hover {
            box-shadow: 0 8px 25px rgba(219, 109, 86, 0.25);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 0.95rem;
        }

        th {
            background: #ffe8e2;
            padding: 14px;
            font-weight: 600;
            color: #a7503e;
            border-bottom: 2px solid #f5d2ca;
        }

        th .icon {
            margin-right: 8px;
            color: #db6d56;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }

        /* Estilos para mensagens */
        .mensagem-nao-lida {
            background: #fff9f9;
            font-weight: 600;
        }

        .mensagem-lida {
            background: #fff;
            opacity: 0.8;
        }

        .conteudo-ofensivo {
            background: #fff5f5 !important;
            border-left: 4px solid #e74c3c;
        }

        .mensagem-conteudo {
            max-width: 300px;
            text-align: left;
        }

        .mensagem-texto {
            max-height: 80px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .aviso-ofensivo {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 5px;
            font-weight: 600;
        }

        .status-mensagem {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-mensagem.lida {
            background: #d4edda;
            color: #155724;
        }

        .status-mensagem.nao-lida {
            background: #fff3cd;
            color: #856404;
        }

        tr:hover td {
            background: #fff6f5;
        }

        /* ===== BOTÕES ===== */
        button {
            cursor: pointer;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .acoes-botoes {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .btn-status {
            background: #3498db;
            color: #fff;
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .btn-status:hover {
            background: #2980b9;
        }

        .delete {
            background: #e74c3c;
            color: #fff;
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .delete:hover {
            background: #c0392b;
        }

        .refresh {
            background: #db6d56;
            color: #fff;
            padding: 12px 20px;
            font-size: 1rem;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(219, 109, 86, 0.3);
        }

        .refresh:hover {
            background: #a7503e;
            transform: scale(1.05);
        }

        /* ===== AÇÕES ===== */
        .actions {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-top: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* ===== HAMBURGUER FLUTUANTE ===== */
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

        #menu-toggle-desktop:checked + .hamburger-desktop + .menu-content-desktop {
            display: block;
        }

        .menu-content-desktop .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #5b4a42;
            padding: 8px 0;
            text-decoration: none;
            font-weight: 500;
        }

        .menu-content-desktop .menu-item.active {
            font-weight: 700;
            color: #db6d56;
        }

        .menu-content-desktop .menu-item i {
            width: 20px;
            text-align: center;
        }

        .menu-content-desktop .menu-item:hover {
            color: #db6d56;
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

            th, td {
                font-size: 0.85rem;
                padding: 10px;
            }

            .acoes-botoes {
                flex-direction: column;
                gap: 3px;
            }

            .table-container {
                width: 95%;
                padding: 15px;
            }

            .mensagem-conteudo {
                max-width: 200px;
            }
        }
    </style>
</body>
</html>