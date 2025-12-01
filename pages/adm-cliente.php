<?php
session_start();
require_once '../config/database.php';

// Processar ações (excluir)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_cliente'])) {
    $cliente_id = $_POST['cliente_id'];
    $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
    $stmt->execute([$cliente_id]);

    // Redirecionar para evitar reenvio do formulário
    header("Location: adm-cliente.php?excluido=1");
    exit;
}

// Processar ações (excluir e editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['excluir_cliente'])) {
        $cliente_id = $_POST['cliente_id'];
        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
        $stmt->execute([$cliente_id]);

        // Redirecionar para evitar reenvio do formulário
        header("Location: adm2.php?excluido=1");
        exit;
    }

    // Processar edição do cliente
    if (isset($_POST['cliente_id']) && isset($_POST['nome']) && isset($_POST['email'])) {
        $cliente_id = $_POST['cliente_id'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'] ?? null;

        // Verificar se o email já existe em outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ? AND ativo = 1");
        $stmt->execute([$email, $cliente_id]);

        if ($stmt->rowCount() > 0) {
            header("Location: adm2.php?erro=email_existente");
            exit;
        }

        // Atualizar os dados do cliente
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $telefone, $cliente_id]);

        // Redirecionar para evitar reenvio do formulário
        header("Location: adm-cliente.php?editado=1");
        exit;
    }
    
    // Processar novo cliente
    if (isset($_POST['novo_cliente']) && isset($_POST['nome']) && isset($_POST['email'])) {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'] ?? null;
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        // Verificar se o email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            header("Location: adm-cliente.php?erro=email_existente");
            exit;
        }

        // Inserir novo cliente
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tipo, ativo) VALUES (?, ?, ?, ?, 'usuario', 1)");
        $stmt->execute([$nome, $email, $telefone, $senha]);

        // Redirecionar para evitar reenvio do formulário
        header("Location: adm-cliente.php?novo=1&nome=" . urlencode($nome));
        exit;
    }
}

// Paginação
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Buscar clientes do banco
$stmt = $pdo->prepare("
    SELECT u.id, u.nome, u.email, u.telefone, u.criado_em, 
           COUNT(p.id) as total_compras 
    FROM usuarios u 
    LEFT JOIN pedidos p ON u.id = p.usuario_id AND p.status = 'pago' 
    WHERE u.tipo = 'usuario' AND u.ativo = 1 
    GROUP BY u.id 
    ORDER BY u.criado_em DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limite, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de clientes para paginação
$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'usuario' AND ativo = 1");
$totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalClientes / $limite);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Verseal</title>
    <script defer src="../js/admhome.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php">Início</a>
            <a href="adm-cliente.php" class="active">Clientes</a>
            <a href="adm-artista.php">Artistas</a>
            <a href="adm-obras.php">Obras</a>
            <a href="adm-contato.php">Contato</a>
            <a href="logout.php">Sair</a>
        </nav>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Clientes</h1>
            <span class="welcome">Gerencie os clientes cadastrados - Total: <?php echo $totalClientes; ?></span>
        </header>

        <section class="content">

            <!-- CONTAINER DA TABELA -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user icon"></i> NOME</th>
                            <th><i class="fas fa-envelope icon"></i> EMAIL</th>
                            <th><i class="fas fa-phone icon"></i> TELEFONE</th>
                            <th><i class="fas fa-shopping-bag icon"></i> COMPRAS</th>
                            <th><i class="fas fa-calendar icon"></i> CADASTRO</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">
                                    Nenhum cliente cadastrado
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td><?php echo $cliente['telefone'] ? htmlspecialchars($cliente['telefone']) : 'Não informado'; ?>
                                    </td>
                                    <td><?php echo $cliente['total_compras']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['criado_em'])); ?></td>
                                    <td>
                                        <div class="acoes-botoes">
                                            <button class="edit"
                                                onclick="editarCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nome']); ?>', '<?php echo htmlspecialchars($cliente['email']); ?>', '<?php echo htmlspecialchars($cliente['telefone']); ?>')">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="delete"
                                                onclick="excluirCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nome']); ?>')">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- PAGINAÇÃO -->
                <?php if ($totalPaginas > 1): ?>
                    <div class="paginacao">
                        <?php if ($pagina > 1): ?>
                            <a href="?pagina=<?php echo $pagina - 1; ?>" class="pagina-btn">&laquo; Anterior</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <a href="?pagina=<?php echo $i; ?>" class="pagina-btn <?php echo $i == $pagina ? 'ativa' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($pagina < $totalPaginas): ?>
                            <a href="?pagina=<?php echo $pagina + 1; ?>" class="pagina-btn">Próxima &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- BOTÕES DE AÇÃO -->
            <div class="actions">
                <button class="refresh" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Atualizar
                </button>
                <button class="new" onclick="novoCliente()">
                    <i class="fas fa-plus"></i> Novo Cliente
                </button>
            </div>
        </section>
    </main>

    <!-- MODAL EDIÇÃO -->
    <div id="modalEdicao" class="modal">
        <div class="modal-content">
            <span class="fechar">&times;</span>
            <h3><i class="fas fa-user-edit"></i> Editar Cliente</h3>
            <form id="formEdicao" method="POST">
                <input type="hidden" id="clienteId" name="cliente_id">

                <div class="campo">
                    <label><i class="fas fa-user"></i> Nome:</label>
                    <input type="text" id="clienteNome" name="nome" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="clienteEmail" name="email" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-phone"></i> Telefone:</label>
                    <input type="text" id="clienteTelefone" name="telefone" placeholder="(00) 00000-0000">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancelar" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-salvar">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL NOVO CLIENTE -->
    <div id="modalNovoCliente" class="modal">
        <div class="modal-content">
            <span class="fechar" onclick="fecharModalNovo()">&times;</span>
            <h3><i class="fas fa-user-plus"></i> Novo Cliente</h3>
            <form id="formNovoCliente" method="POST">
                <input type="hidden" name="novo_cliente" value="1">

                <div class="campo">
                    <label><i class="fas fa-user"></i> Nome:</label>
                    <input type="text" name="nome" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-phone"></i> Telefone:</label>
                    <input type="text" name="telefone" placeholder="(00) 00000-0000">
                </div>
                <div class="campo">
                    <label><i class="fas fa-lock"></i> Senha:</label>
                    <input type="password" name="senha" required minlength="6">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancelar" onclick="fecharModalNovo()">Cancelar</button>
                    <button type="submit" class="btn-salvar">
                        <i class="fas fa-user-plus"></i> Cadastrar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FORMULÁRIO INVISÍVEL PARA EXCLUSÃO -->
    <form id="formExcluir" method="POST" style="display: none;">
        <input type="hidden" id="clienteExcluirId" name="cliente_id">
        <input type="hidden" name="excluir_cliente" value="1">
    </form>

    <script>
        // Modal de edição
        const modal = document.getElementById('modalEdicao');
        const modalNovo = document.getElementById('modalNovoCliente');
        const fecharBtn = document.querySelector('.fechar');
        const formExcluir = document.getElementById('formExcluir');
        const formEdicao = document.getElementById('formEdicao');
        const formNovoCliente = document.getElementById('formNovoCliente');

        function editarCliente(id, nome, email, telefone) {
            document.getElementById('clienteId').value = id;
            document.getElementById('clienteNome').value = nome;
            document.getElementById('clienteEmail').value = email;
            document.getElementById('clienteTelefone').value = telefone || '';
            modal.style.display = 'block';

            // Prevenir comportamento padrão
            return false;
        }

        function novoCliente() {
            modalNovo.style.display = 'block';
            return false;
        }

        function excluirCliente(id, nome) {
            Swal.fire({
                title: 'Excluir Cliente',
                html: `Tem certeza que deseja excluir o cliente <strong>"${nome}"</strong>?`,
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
                    document.getElementById('clienteExcluirId').value = id;
                    formExcluir.submit();
                }
            });

            // Prevenir comportamento padrão
            return false;
        }

        function fecharModal() {
            modal.style.display = 'none';
        }

        function fecharModalNovo() {
            modalNovo.style.display = 'none';
        }

        // Submissão do formulário de edição
        formEdicao.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            Swal.fire({
                title: 'Salvar alterações?',
                text: 'As informações do cliente serão atualizadas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#db6d56',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sim, salvar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Submissão do formulário de novo cliente
        formNovoCliente.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            Swal.fire({
                title: 'Cadastrar cliente?',
                text: 'Um novo cliente será adicionado ao sistema.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#db6d56',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sim, cadastrar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        fecharBtn.onclick = fecharModal;

        window.onclick = function (event) {
            if (event.target == modal) {
                fecharModal();
            }
            if (event.target == modalNovo) {
                fecharModalNovo();
            }
        }

        // SweetAlert para notificações
        <?php if (isset($_GET['excluido']) && $_GET['excluido'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Cliente excluído!',
                text: 'O cliente foi excluído com sucesso.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['editado']) && $_GET['editado'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Alterações salvas!',
                text: 'Os dados do cliente foram atualizados com sucesso.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['novo']) && $_GET['novo'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Cliente cadastrado!',
                html: 'Cliente <strong><?php echo isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : ''; ?></strong> cadastrado com sucesso!',
                timer: 3000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'email_existente'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Email já existe!',
                text: 'Este email já está cadastrado para outro usuário.',
                timer: 3000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        document.addEventListener('click', function (e) {
            const toggle = document.getElementById('menu-toggle-desktop');
            if (!e.target.closest('.hamburger-menu-desktop')) {
                toggle.checked = false;
            }
        });
    </script>

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

        tr:hover td {
            background: #fff6f5;
        }

        /* ===== BOTÕES AÇÕES ===== */
        .acoes-botoes {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

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

        .edit {
            background: #ffb347;
            color: #fff;
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .edit:hover {
            background: #e89c30;
            transform: translateY(-2px);
        }

        .delete {
            background: #e74c3c;
            color: #fff;
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        /* ===== BOTÕES PRINCIPAIS ===== */
        .refresh,
        .new {
            background: #db6d56;
            color: #fff;
            padding: 12px 20px;
            font-size: 1rem;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(219, 109, 86, 0.3);
        }

        .refresh:hover,
        .new:hover {
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

        /* ===== PAGINAÇÃO ===== */
        .paginacao {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 8px;
        }

        .pagina-btn {
            padding: 8px 12px;
            background: #f0e0de;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }

        .pagina-btn:hover {
            background: #db6d56;
            color: white;
        }

        .pagina-btn.ativa {
            background: #db6d56;
            color: white;
        }

        /* ===== MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content h3 {
            color: #db6d56;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fechar {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            top: 15px;
            right: 20px;
        }

        .fechar:hover {
            color: #db6d56;
        }

        .campo {
            margin-bottom: 20px;
        }

        .campo label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .campo label i {
            margin-right: 8px;
            color: #db6d56;
        }

        .campo input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background: #f5f5f5;
            transition: all 0.3s ease;
        }

        .campo input:focus {
            border-color: #db6d56;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(219, 109, 86, 0.2);
            outline: none;
        }

        .campo input:read-only {
            background: #f9f9f9;
            color: #666;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
        }

        .btn-cancelar {
            background: #95a5a6;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-cancelar:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        .btn-salvar {
            background: #27ae60;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-salvar:hover {
            background: #219653;
            transform: translateY(-2px);
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

            th,
            td {
                font-size: 0.85rem;
                padding: 10px;
            }

            .acoes-botoes {
                flex-direction: column;
                gap: 5px;
            }

            .table-container {
                width: 95%;
                padding: 15px;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>
</body>

</html>