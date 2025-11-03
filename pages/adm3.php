<?php
session_start();
require_once '../config/database.php';

// Processar ações (excluir e editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['excluir_artista'])) {
        $artista_id = $_POST['artista_id'];
        $stmt = $pdo->prepare("UPDATE artistas SET ativo = 0 WHERE id = ?");
        $stmt->execute([$artista_id]);
        
        header("Location: adm3.php?excluido=1");
        exit;
    }
    
    if (isset($_POST['editar_artista'])) {
        $artista_id = $_POST['artista_id'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
        
        // Processar upload de imagem
        $imagem_perfil = $_POST['imagem_atual']; // manter a imagem atual
        
        if (isset($_FILES['imagem_perfil']) && $_FILES['imagem_perfil']['error'] === 0) {
            $arquivo = $_FILES['imagem_perfil'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $pasta_upload = '../img/artistas/';
                if (!is_dir($pasta_upload)) {
                    mkdir($pasta_upload, 0777, true);
                }
                
                $nome_arquivo = 'artista_' . $artista_id . '_' . time() . '.' . $extensao;
                $caminho_completo = $pasta_upload . $nome_arquivo;
                
                if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                    // Remover imagem antiga se existir
                    if ($_POST['imagem_atual'] && file_exists('../' . $_POST['imagem_atual'])) {
                        unlink('../' . $_POST['imagem_atual']);
                    }
                    $imagem_perfil = 'img/artistas/' . $nome_arquivo;
                }
            }
        }
        
        try {
            $stmt = $pdo->prepare("
                UPDATE artistas 
                SET nome = ?, email = ?, telefone = ?, imagem_perfil = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nome, $email, $telefone, $imagem_perfil, $artista_id]);
            
            header("Location: adm3.php?editado=1");
            exit;
            
        } catch (PDOException $e) {
            header("Location: adm3.php?erro=1");
            exit;
        }
    }
}

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Buscar artistas do banco
try {
    $stmt = $pdo->prepare("
        SELECT * FROM artistas 
        WHERE ativo = 1 
        ORDER BY nome ASC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limite, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar obras para cada artista
    foreach ($artistas as &$artista) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_obras 
            FROM produtos 
            WHERE artista = ? AND ativo = 1
        ");
        $stmt->execute([$artista['nome']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $artista['total_obras'] = $result['total_obras'];
    }

} catch (PDOException $e) {
    $stmt = $pdo->prepare("
        SELECT * FROM artistas 
        WHERE ativo = 1 
        ORDER BY nome ASC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limite, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($artistas as &$artista) {
        $artista['total_obras'] = 0;
    }
}

// Total de artistas para paginação
$stmt = $pdo->query("SELECT COUNT(*) as total FROM artistas WHERE ativo = 1");
$totalArtistas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalArtistas / $limite);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artistas - Verseal</title>
    <script defer src="../js/admhome.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php">Início</a>
            <a href="adm2.php">Clientes</a>
            <a href="adm3.php" class="active">Artistas</a>
            <a href="adm4.php">Obras</a>
            <a href="adm5.php">Contato</a>
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
            <h1>Artistas</h1>
            <span class="welcome">Gerencie os artistas cadastrados - Total: <?php echo $totalArtistas; ?></span>
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
                            <th><i class="fas fa-palette icon"></i> OBRAS</th>
                            <th><i class="fas fa-image icon"></i> IMAGEM</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($artistas)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                Nenhum artista cadastrado
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($artistas as $artista): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($artista['nome']); ?></td>
                                <td><?php echo htmlspecialchars($artista['email']); ?></td>
                                <td><?php echo $artista['telefone'] ? htmlspecialchars($artista['telefone']) : 'Não informado'; ?></td>
                                <td><?php echo $artista['total_obras']; ?></td>
                                <td>
                                    <?php if (!empty($artista['imagem_perfil'])): ?>
                                        <img src="../<?php echo $artista['imagem_perfil']; ?>" 
                                             alt="<?php echo htmlspecialchars($artista['nome']); ?>" 
                                             class="imagem-artista"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="sem-imagem" style="display: none;">
                                            <i class="fas fa-user-circle"></i>
                                            <span>Erro ao carregar</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="sem-imagem">
                                            <i class="fas fa-user-circle"></i>
                                            <span>Sem imagem</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="acoes-botoes">
                                        <button class="edit" onclick="abrirModalEditar(
                                            <?php echo $artista['id']; ?>, 
                                            '<?php echo htmlspecialchars($artista['nome']); ?>', 
                                            '<?php echo htmlspecialchars($artista['email']); ?>', 
                                            '<?php echo htmlspecialchars($artista['telefone']); ?>', 
                                            '<?php echo !empty($artista['imagem_perfil']) ? $artista['imagem_perfil'] : ''; ?>'
                                        )">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button class="delete" onclick="excluirArtista(<?php echo $artista['id']; ?>, '<?php echo htmlspecialchars($artista['nome']); ?>')">
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
                <a href="novo_artista_adm.php" style="text-decoration: none; display: inline-block;">
                    <button class="new">
                        <i class="fas fa-plus"></i> Novo Artista
                    </button>
                </a>
            </div>
        </section>
    </main>

    <!-- MODAL DE EDIÇÃO -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Artista</h2>
                <span class="close" onclick="fecharModalEditar()">&times;</span>
            </div>
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="artista_id" id="editar_artista_id">
                <input type="hidden" name="imagem_atual" id="editar_imagem_atual">
                <input type="hidden" name="editar_artista" value="1">
                
                <div class="form-group">
                    <label for="editar_nome">Nome</label>
                    <input type="text" id="editar_nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="editar_email">Email</label>
                    <input type="email" id="editar_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="editar_telefone">Telefone</label>
                    <input type="text" id="editar_telefone" name="telefone">
                </div>
                
                <div class="form-group">
                    <label for="editar_imagem_perfil">Imagem de Perfil</label>
                    <input type="file" id="editar_imagem_perfil" name="imagem_perfil" accept="image/*">
                    <small>Formatos: JPG, PNG, GIF. Deixe em branco para manter a imagem atual.</small>
                </div>
                
                <div class="preview-imagem">
                    <img id="preview_imagem" src="" alt="Preview" style="display: none;">
                    <div id="sem_preview" class="sem-imagem" style="display: none;">
                        <i class="fas fa-user-circle"></i>
                        <span>Sem imagem</span>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancelar" onclick="fecharModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-salvar">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FORMULÁRIO INVISÍVEL PARA EXCLUSÃO -->
    <form id="formExcluir" method="POST" style="display: none;">
        <input type="hidden" id="artistaExcluirId" name="artista_id">
        <input type="hidden" name="excluir_artista" value="1">
    </form>

    <script>
        // Função para abrir modal de edição
        function abrirModalEditar(id, nome, email, telefone, imagem) {
            document.getElementById('editar_artista_id').value = id;
            document.getElementById('editar_nome').value = nome;
            document.getElementById('editar_email').value = email;
            document.getElementById('editar_telefone').value = telefone;
            document.getElementById('editar_imagem_atual').value = imagem;
            
            // Preview da imagem atual
            const previewImg = document.getElementById('preview_imagem');
            const semPreview = document.getElementById('sem_preview');
            
            if (imagem && imagem.trim() !== '') {
                previewImg.src = '../' + imagem;
                previewImg.style.display = 'block';
                semPreview.style.display = 'none';
            } else {
                previewImg.style.display = 'none';
                semPreview.style.display = 'flex';
            }
            
            document.getElementById('modalEditar').style.display = 'block';
        }

        // Função para fechar modal
        function fecharModalEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Preview de nova imagem selecionada
        document.getElementById('editar_imagem_perfil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewImg = document.getElementById('preview_imagem');
            const semPreview = document.getElementById('sem_preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    semPreview.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target === modal) {
                fecharModalEditar();
            }
        }

        // Função para excluir artista
        function excluirArtista(id, nome) {
            Swal.fire({
                title: 'Excluir Artista',
                html: `Tem certeza que deseja excluir o artista <strong>"${nome}"</strong>?<br><small>Todas as obras deste artista também serão removidas.</small>`,
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
                    document.getElementById('artistaExcluirId').value = id;
                    document.getElementById('formExcluir').submit();
                }
            });
            
            return false;
        }

        // SweetAlert para ações
        <?php if (isset($_GET['excluido']) && $_GET['excluido'] == 1): ?>
        Swal.fire({
            icon: 'success',
            title: 'Artista excluído!',
            text: 'O artista foi excluído com sucesso.',
            timer: 2000,
            showConfirmButton: false,
            background: '#fff'
        });
        <?php endif; ?>

        <?php if (isset($_GET['editado']) && $_GET['editado'] == 1): ?>
        Swal.fire({
            icon: 'success',
            title: 'Artista atualizado!',
            text: 'As alterações foram salvas com sucesso.',
            timer: 2000,
            showConfirmButton: false,
            background: '#fff'
        });
        <?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] == 1): ?>
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Ocorreu um erro ao atualizar o artista.',
            timer: 2000,
            showConfirmButton: false,
            background: '#fff'
        });
        <?php endif; ?>

        // Menu hamburguer
        document.addEventListener('click', function(e) {
            const toggle = document.getElementById('menu-toggle-desktop');
            if(!e.target.closest('.hamburger-menu-desktop')) {
                toggle.checked = false;
            }
        });
    </script>

    <style>
        /* ===== RESET ===== */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { display:flex; height:100vh; background:linear-gradient(135deg,#f8f4f2,#fff9f8); color:#333; overflow:hidden; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width:250px; height:100vh; background:linear-gradient(180deg, rgba(219,109,86,0.95), rgba(167,80,62,0.95));
            backdrop-filter:blur(6px); color:#fff; display:flex; flex-direction:column; align-items:center; padding:35px 20px;
            box-shadow:3px 0 15px rgba(0,0,0,0.15); border-right:2px solid rgba(255,255,255,0.15);
        }
        .logo { font-family:'Playfair Display', serif; font-size:2.6rem; color:#fdfdfd; letter-spacing:5px; font-style:italic; text-shadow:2px 2px 8px rgba(0,0,0,0.3); margin-bottom:40px; cursor:default; user-select:none; transition: all 0.3s ease; }
        .logo:hover { color:#fff3f0; text-shadow:3px 3px 10px #db6d56; }
        .menu { display:flex; flex-direction:column; width:100%; }
        .menu a { text-decoration:none; color:#fff; padding:12px 20px; border-radius:10px; margin-bottom:10px; transition: all 0.3s ease; font-weight:500; }
        .menu a:hover, .menu a.active { background: rgba(255,255,255,0.25); transform: translateX(4px); box-shadow:0 2px 10px rgba(255,255,255,0.1); }

        /* ===== DASHBOARD ===== */
        .dashboard { flex:1; padding:40px 50px; overflow-y:auto; animation:fadeIn 0.8s ease; }
        @keyframes fadeIn { from{opacity:0; transform:translateY(15px);} to{opacity:1; transform:translateY(0);} }

        /* ===== TOPBAR ===== */
        .topbar { display:flex; flex-direction:column; margin-bottom:25px; border-bottom:2px solid #f0e0de; padding-bottom:10px; }
        .topbar h1 { font-size:2rem; color:#db6d56; margin-bottom:6px; text-shadow:1px 1px 4px rgba(219,109,86,0.3); }
        .topbar .welcome { font-size:1rem; color:#666; font-style:italic; }

        /* ===== TABELA ===== */
        .table-container { background:#fff; border-radius:20px; padding:25px; box-shadow:0 6px 20px rgba(0,0,0,0.08); width:90%; max-width:1200px; margin:0 auto 45px auto; overflow-x:auto; transition:0.3s ease; }
        .table-container:hover { box-shadow:0 8px 25px rgba(219,109,86,0.25); }
        table { width:100%; border-collapse:collapse; text-align:center; font-size:0.95rem; }
        th { background:#ffe8e2; padding:14px; font-weight:600; color:#a7503e; border-bottom:2px solid #f5d2ca; }
        th .icon { margin-right:8px; color:#db6d56; }
        td { padding:14px; border-bottom:1px solid #eee; transition: background 0.3s ease; }
        tr:hover td { background:#fff6f5; }

        /* ===== IMAGEM DO ARTISTA ===== */
        .imagem-artista {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #db6d56;
        }

        .sem-imagem {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: #999;
            font-size: 0.8rem;
        }

        .sem-imagem i {
            font-size: 1.5rem;
            color: #ccc;
        }

        /* ===== BOTÕES AÇÕES ===== */
        .acoes-botoes { display: flex; gap: 8px; justify-content: center; }
        button { cursor:pointer; border:none; border-radius:8px; font-weight:600; transition:all 0.3s ease; display: flex; align-items: center; gap: 5px; }
        .edit { background:#ffb347; color:#fff; padding:8px 12px; font-size:0.85rem; }
        .edit:hover { background:#e89c30; transform: translateY(-2px); }
        .delete { background:#e74c3c; color:#fff; padding:8px 12px; font-size:0.85rem; }
        .delete:hover { background:#c0392b; transform: translateY(-2px); }

        /* ===== BOTÕES PRINCIPAIS ===== */
        .refresh, .new { background:#db6d56; color:#fff; padding:12px 20px; font-size:1rem; border-radius:12px; box-shadow:0 3px 10px rgba(219,109,86,0.3); }
        .refresh:hover, .new:hover { background:#a7503e; transform:scale(1.05); }

        /* ===== AÇÕES ===== */
        .actions { display:flex; justify-content:flex-start; align-items:center; margin-top:25px; gap:15px; flex-wrap:wrap; }

        /* ===== PAGINAÇÃO ===== */
        .paginacao { display: flex; justify-content: center; align-items: center; margin-top: 20px; gap: 8px; }
        .pagina-btn { padding: 8px 12px; background: #f0e0de; color: #333; text-decoration: none; border-radius: 6px; transition: 0.3s; }
        .pagina-btn:hover { background: #db6d56; color: white; }
        .pagina-btn.ativa { background: #db6d56; color: white; }

        /* ===== MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            background: linear-gradient(135deg, #db6d56, #a7503e);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .close:hover {
            color: #ffe8e2;
        }

        .modal-content form {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: #db6d56;
            outline: none;
        }

        .form-group small {
            color: #888;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }

        .preview-imagem {
            text-align: center;
            margin: 15px 0;
        }

        .preview-imagem img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
            border: 2px solid #db6d56;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .btn-cancelar {
            background: #95a5a6;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-cancelar:hover {
            background: #7f8c8d;
        }

        .btn-salvar {
            background: #db6d56;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-salvar:hover {
            background: #a7503e;
        }

        /* ===== HAMBURGUER FLUTUANTE ===== */
        .hamburger-menu-desktop { position:absolute; top:15px; right:30px; z-index:999; }
        .hamburger-desktop { display:flex; align-items:center; gap:8px; background:linear-gradient(135deg,#e07b67,#cc624e); color:white; padding:10px 18px; border-radius:40px; cursor:pointer; box-shadow:0 4px 10px rgba(204,98,78,0.4); transition:0.3s; }
        .hamburger-desktop:hover { background:linear-gradient(135deg,#cc624e,#e07b67); transform:translateY(-2px); }
        .hamburger-desktop i { font-size:1.1rem; }
        #menu-toggle-desktop { display:none; }
        .menu-content-desktop { display:none; position:absolute; top:60px; right:0; background:#fff; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.15); padding:15px 20px; width:180px; }
        #menu-toggle-desktop:checked + .hamburger-desktop + .menu-content-desktop { display:block; }
        .menu-content-desktop .menu-item { display:flex; align-items:center; gap:10px; color:#5b4a42; padding:8px 0; text-decoration:none; font-weight:500; }
        .menu-content-desktop .menu-item.active { font-weight:700; color:#db6d56; }
        .menu-content-desktop .menu-item i { width:20px; text-align:center; }
        .menu-content-desktop .menu-item:hover { color:#db6d56; }
  
        /* ===== RESPONSIVIDADE ===== */
        @media (max-width:950px) {
            body { flex-direction:column; }
            .sidebar { width:100%; flex-direction:row; justify-content:space-around; padding:15px; height:auto; }
            .menu { flex-direction:row; flex-wrap:wrap; justify-content:center; }
            .menu a { margin:5px; padding:8px 12px; font-size:0.9rem; }
            .dashboard { padding:20px; }
            th, td { font-size:0.85rem; padding: 10px; }
            .acoes-botoes { flex-direction: column; gap: 5px; }
            .table-container { width: 95%; padding: 15px; }
            .imagem-artista { width: 40px; height: 40px; }
            .modal-content { margin: 10% auto; width: 95%; }
        }
    </style>
</body>
</html>