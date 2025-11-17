<?php
session_start();
require_once '../config/database.php';

// Verificar se é admin
if (!isset($_SESSION["tipo_usuario"]) || $_SESSION["tipo_usuario"] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Processar ações (excluir, editar, nova obra)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PROCESSAR EXCLUSÃO DE OBRA
    if (isset($_POST['excluir_obra'])) {
        $produto_id = $_POST['produto_id'];

        try {
            // Buscar imagem da obra para excluir
            $stmt = $pdo->prepare("SELECT imagem_url FROM produtos WHERE id = ?");
            $stmt->execute([$produto_id]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            // Excluir arquivo de imagem se existir
            if ($produto && $produto['imagem_url'] && file_exists('../' . $produto['imagem_url'])) {
                unlink('../' . $produto['imagem_url']);
            }

            // Excluir do banco
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$produto_id]);

            header("Location: adm-obras.php?excluido=1");
            exit;

        } catch (PDOException $e) {
            header("Location: adm-obras.php?erro_exclusao=1");
            exit;
        }
    }

    // PROCESSAR EDIÇÃO DE OBRA
    if (isset($_POST['editar_obra'])) {
        $produto_id = $_POST['produto_id'];
        $nome = $_POST['nome'];
        $artista = $_POST['artista'];
        $preco = $_POST['preco'];
        $descricao = $_POST['descricao'];
        $dimensoes = $_POST['dimensoes'];
        $tecnica = $_POST['tecnica'];
        $ano = $_POST['ano'];
        $material = $_POST['material'];
        $estoque = $_POST['estoque'];
        $destaque = isset($_POST['destaque']) ? 1 : 0;

        // Processar categorias
        $categorias = [];
        if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
            $categorias = $_POST['categorias'];
        }
        $categorias_json = json_encode($categorias);

        // Processar upload de imagem
        $imagem_url = $_POST['imagem_atual']; // manter a imagem atual por padrão

        if (isset($_FILES['imagem_url']) && $_FILES['imagem_url']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['imagem_url'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($extensao, $extensoes_permitidas)) {
                // Criar pasta de uploads se não existir
                $pasta_upload = '../img/uploads/';
                if (!is_dir($pasta_upload)) {
                    mkdir($pasta_upload, 0755, true);
                }

                // Gerar nome único para o arquivo
                $nome_arquivo = 'obra_' . $produto_id . '_' . time() . '.' . $extensao;
                $caminho_completo = $pasta_upload . $nome_arquivo;

                if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                    // Remover imagem antiga se existir
                    if (!empty($_POST['imagem_atual']) && file_exists('../' . $_POST['imagem_atual'])) {
                        unlink('../' . $_POST['imagem_atual']);
                    }
                    $imagem_url = 'img/uploads/' . $nome_arquivo;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE produtos 
                SET nome = ?, artista = ?, preco = ?, descricao = ?, dimensoes = ?, 
                    tecnica = ?, ano = ?, material = ?, categorias = ?, imagem_url = ?, 
                    estoque = ?, destaque = ?, data_atualizacao = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $nome, $artista, $preco, $descricao, $dimensoes, 
                $tecnica, $ano, $material, $categorias_json, $imagem_url,
                $estoque, $destaque, $produto_id
            ]);

            header("Location: adm-obras.php?editado=1");
            exit;

        } catch (PDOException $e) {
            header("Location: adm-obras.php?erro=1");
            exit;
        }
    }

    // PROCESSAR NOVA OBRA
    if (isset($_POST['nova_obra'])) {
        $nome = $_POST['nome'];
        $artista = $_POST['artista'];
        $preco = $_POST['preco'];
        $descricao = $_POST['descricao'];
        $dimensoes = $_POST['dimensoes'];
        $tecnica = $_POST['tecnica'];
        $ano = $_POST['ano'];
        $material = $_POST['material'];
        $estoque = $_POST['estoque'];
        $destaque = isset($_POST['destaque']) ? 1 : 0;

        // Processar categorias
        $categorias = [];
        if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
            $categorias = $_POST['categorias'];
        }
        $categorias_json = json_encode($categorias);

        // Processar upload de imagem
        $imagem_url = null;

        if (isset($_FILES['imagem_url']) && $_FILES['imagem_url']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['imagem_url'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($extensao, $extensoes_permitidas)) {
                // Criar pasta de uploads se não existir
                $pasta_upload = '../img/uploads/';
                if (!is_dir($pasta_upload)) {
                    mkdir($pasta_upload, 0755, true);
                }

                // Gerar nome único para o arquivo
                $nome_arquivo = 'obra_' . time() . '_' . uniqid() . '.' . $extensao;
                $caminho_completo = $pasta_upload . $nome_arquivo;

                if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                    $imagem_url = 'img/uploads/' . $nome_arquivo;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO produtos 
                (nome, artista, preco, descricao, dimensoes, tecnica, ano, material, 
                 categorias, imagem_url, estoque, destaque, ativo, data_cadastro, data_atualizacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->execute([
                $nome, $artista, $preco, $descricao, $dimensoes, 
                $tecnica, $ano, $material, $categorias_json, $imagem_url,
                $estoque, $destaque
            ]);

            header("Location: adm-obras.php?nova_sucesso=1&nome=" . urlencode($nome));
            exit;

        } catch (PDOException $e) {
            header("Location: adm-obras.php?nova_erro=1");
            exit;
        }
    }
}

// Paginação
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Buscar obras do banco
try {
    $stmt = $pdo->prepare("
        SELECT * FROM produtos 
        ORDER BY data_cadastro DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limite, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $obras = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro na consulta de obras: " . $e->getMessage());
    $obras = [];
}

// Total de obras para paginação
$stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos");
$totalObras = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalObras / $limite);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obras - Verseal</title>
    
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
            <a href="adm-cliente.php">Clientes</a>
            <a href="adm-artista.php">Artistas</a>
            <a href="adm-obras.php" class="active">Obras</a>
            <a href="adm-contato.php">Contato</a>
        </nav>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Obras</h1>
            <span class="welcome">Gerencie as obras cadastradas - Total: <?php echo $totalObras; ?></span>
        </header>

        <section class="content">
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

            <!-- CONTAINER DA TABELA -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-pencil-alt icon"></i> TÍTULO</th>
                            <th><i class="fas fa-palette icon"></i> ARTISTA</th>
                            <th><i class="fas fa-tag icon"></i> PREÇO</th>
                            <th><i class="fas fa-image icon"></i> IMAGEM</th>
                            <th><i class="fas fa-box icon"></i> ESTOQUE</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($obras)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">
                                    Nenhuma obra cadastrada
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($obras as $obra): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($obra['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($obra['artista']); ?></td>
                                    <td>R$ <?php echo number_format($obra['preco'], 2, ',', '.'); ?></td>
                                    <td>
                                        <?php if (!empty($obra['imagem_url'])): ?>
                                            <img src="../<?php echo $obra['imagem_url']; ?>"
                                                alt="<?php echo htmlspecialchars($obra['nome']); ?>" class="obra-img"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="sem-imagem" style="display: none;">
                                                <i class="fas fa-image"></i>
                                                <span>Erro ao carregar</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="sem-imagem">
                                                <i class="fas fa-image"></i>
                                                <span>Sem imagem</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $obra['estoque']; ?></td>
                                    <td>
                                        <div class="acoes-botoes">
                                            <button class="edit" onclick="abrirModalEditarObra(
                                                <?php echo $obra['id']; ?>, 
                                                '<?php echo addslashes(htmlspecialchars($obra['nome'])); ?>', 
                                                '<?php echo addslashes(htmlspecialchars($obra['artista'])); ?>', 
                                                '<?php echo $obra['preco']; ?>', 
                                                `<?php echo addslashes(str_replace(["\r", "\n"], '', $obra['descricao'])); ?>`, 
                                                '<?php echo addslashes(htmlspecialchars($obra['dimensoes'])); ?>', 
                                                '<?php echo addslashes(htmlspecialchars($obra['tecnica'])); ?>', 
                                                '<?php echo $obra['ano']; ?>', 
                                                '<?php echo addslashes(htmlspecialchars($obra['material'])); ?>', 
                                                <?php echo $obra['estoque']; ?>, 
                                                <?php echo $obra['destaque']; ?>, 
                                                '<?php echo !empty($obra['imagem_url']) ? addslashes($obra['imagem_url']) : ''; ?>',
                                                `<?php echo addslashes($obra['categorias']); ?>`
                                            )">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="delete"
                                                onclick="excluirObra(<?php echo $obra['id']; ?>, '<?php echo htmlspecialchars($obra['nome']); ?>')">
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
                <button class="new" onclick="abrirModalNovaObra()">
                    <i class="fas fa-plus"></i> Nova Obra
                </button>
            </div>
        </section>
    </main>

    <!-- MODAL DE EDIÇÃO -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Obra</h2>
                <span class="close" onclick="fecharModalEditar()">&times;</span>
            </div>
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="produto_id" id="editar_produto_id">
                <input type="hidden" name="imagem_atual" id="editar_imagem_atual">
                <input type="hidden" name="editar_obra" value="1">

                <div class="form-columns">
                    <div class="form-column">
                        <div class="campo">
                            <label><i class="fas fa-pencil-alt"></i> Título: *</label>
                            <input type="text" id="editar_nome" name="nome" required>
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-palette"></i> Artista: *</label>
                            <input type="text" id="editar_artista" name="artista" required>
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-tag"></i> Preço: *</label>
                            <input type="number" id="editar_preco" name="preco" step="0.01" min="0" required>
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-box"></i> Estoque: *</label>
                            <input type="number" id="editar_estoque" name="estoque" min="0" required>
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-calendar"></i> Ano:</label>
                            <input type="number" id="editar_ano" name="ano" min="1000" max="2030">
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="campo">
                            <label><i class="fas fa-ruler-combined"></i> Dimensões:</label>
                            <input type="text" id="editar_dimensoes" name="dimensoes" placeholder="ex: 50x70cm">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-brush"></i> Técnica:</label>
                            <input type="text" id="editar_tecnica" name="tecnica" placeholder="ex: Óleo sobre tela">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-paint-brush"></i> Material:</label>
                            <input type="text" id="editar_material" name="material" placeholder="ex: Tinta acrílica">
                        </div>

                        <div class="campo">
                            <label>
                                <input type="checkbox" id="editar_destaque" name="destaque" value="1">
                                <i class="fas fa-star"></i> Destacar obra
                            </label>
                        </div>
                    </div>
                </div>

                <div class="campo">
                    <label><i class="fas fa-align-left"></i> Descrição:</label>
                    <textarea id="editar_descricao" name="descricao" rows="4" placeholder="Descrição detalhada da obra..."></textarea>
                </div>

                <div class="campo">
                    <label><i class="fas fa-tags"></i> Categorias:</label>
                    <div class="categorias-container">
                        <label><input type="checkbox" name="categorias[]" value="manual"> Manual</label>
                        <label><input type="checkbox" name="categorias[]" value="digital"> Digital</label>
                        <label><input type="checkbox" name="categorias[]" value="colorido"> Colorido</label>
                        <label><input type="checkbox" name="categorias[]" value="preto e branco"> Preto e Branco</label>
                    </div>
                </div>

                <div class="campo">
                    <label><i class="fas fa-image"></i> Imagem:</label>
                    <div class="file-input-container">
                        <input type="file" id="editar_imagem_url" name="imagem_url" accept="image/*"
                            style="display: none;">
                        <button type="button" class="btn-selecionar-imagem"
                            onclick="document.getElementById('editar_imagem_url').click()">
                            <i class="fas fa-folder-open"></i> Selecionar Nova Imagem
                        </button>
                        <span id="nome_arquivo_editar" class="nome-arquivo">Manter imagem atual</span>
                    </div>
                    <small>Formatos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB</small>
                </div>

                <div class="preview-imagem">
                    <img id="preview_imagem" src="" alt="Preview" style="display: none;">
                    <div id="sem_preview" class="sem-imagem" style="display: none;">
                        <i class="fas fa-image"></i>
                        <span>Sem imagem</span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancelar" onclick="fecharModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-salvar">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE NOVA OBRA -->
    <div id="modalNovaObra" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Nova Obra</h2>
                <span class="close" onclick="fecharModalNovaObra()">&times;</span>
            </div>
            <form id="formNovaObra" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="nova_obra" value="1">

                <div class="form-columns">
                    <div class="form-column">
                        <div class="campo">
                            <label><i class="fas fa-pencil-alt"></i> Título: *</label>
                            <input type="text" name="nome" required placeholder="Nome da obra">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-palette"></i> Artista: *</label>
                            <input type="text" name="artista" required placeholder="Nome do artista">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-tag"></i> Preço: *</label>
                            <input type="number" name="preco" step="0.01" min="0" required placeholder="0.00">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-box"></i> Estoque: *</label>
                            <input type="number" name="estoque" min="0" required placeholder="Quantidade disponível">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-calendar"></i> Ano:</label>
                            <input type="number" name="ano" min="1000" max="2030" placeholder="Ano de criação">
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="campo">
                            <label><i class="fas fa-ruler-combined"></i> Dimensões:</label>
                            <input type="text" name="dimensoes" placeholder="ex: 50x70cm">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-brush"></i> Técnica:</label>
                            <input type="text" name="tecnica" placeholder="ex: Óleo sobre tela">
                        </div>

                        <div class="campo">
                            <label><i class="fas fa-paint-brush"></i> Material:</label>
                            <input type="text" name="material" placeholder="ex: Tinta acrílica">
                        </div>

                        <div class="campo">
                            <label>
                                <input type="checkbox" name="destaque" value="1">
                                <i class="fas fa-star"></i> Destacar obra
                            </label>
                        </div>
                    </div>
                </div>

                <div class="campo">
                    <label><i class="fas fa-align-left"></i> Descrição:</label>
                    <textarea name="descricao" rows="4" placeholder="Descrição detalhada da obra..."></textarea>
                </div>

                <div class="campo">
                    <label><i class="fas fa-tags"></i> Categorias:</label>
                    <div class="categorias-container">
                        <label><input type="checkbox" name="categorias[]" value="manual"> Manual</label>
                        <label><input type="checkbox" name="categorias[]" value="digital"> Digital</label>
                        <label><input type="checkbox" name="categorias[]" value="colorido"> Colorido</label>
                        <label><input type="checkbox" name="categorias[]" value="preto e branco"> Preto e Branco</label>
                    </div>
                </div>

                <div class="campo">
                    <label><i class="fas fa-image"></i> Imagem: *</label>
                    <div class="file-input-container">
                        <input type="file" name="imagem_url" id="nova_imagem_url" accept="image/*" required
                            style="display: none;">
                        <button type="button" class="btn-selecionar-imagem"
                            onclick="document.getElementById('nova_imagem_url').click()">
                            <i class="fas fa-folder-open"></i> Selecionar Imagem
                        </button>
                        <span id="nome_arquivo_novo" class="nome-arquivo">Nenhum arquivo selecionado</span>
                    </div>
                    <small>Formatos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB</small>
                </div>

                <div class="preview-imagem">
                    <img id="preview_nova_imagem" src="" alt="Preview" style="display: none;">
                    <div id="sem_preview_novo" class="sem-imagem">
                        <i class="fas fa-image"></i>
                        <span>Prévia da imagem aparecerá aqui</span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancelar" onclick="fecharModalNovaObra()">Cancelar</button>
                    <button type="submit" class="btn-salvar">
                        <i class="fas fa-save"></i> Cadastrar Obra
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FORMULÁRIO INVISÍVEL PARA EXCLUSÃO -->
    <form id="formExcluir" method="POST" style="display: none;">
        <input type="hidden" id="produtoExcluirId" name="produto_id">
        <input type="hidden" name="excluir_obra" value="1">
    </form>

    <script>
        // Função para abrir modal de edição
        function abrirModalEditarObra(id, nome, artista, preco, descricao, dimensoes, tecnica, ano, material, estoque, destaque, imagem, categorias) {
            console.log('Abrindo modal de edição para obra ID:', id);
            
            try {
                // Preencher os campos do formulário
                document.getElementById('editar_produto_id').value = id;
                document.getElementById('editar_nome').value = nome;
                document.getElementById('editar_artista').value = artista;
                document.getElementById('editar_preco').value = preco;
                document.getElementById('editar_descricao').value = descricao || '';
                document.getElementById('editar_dimensoes').value = dimensoes || '';
                document.getElementById('editar_tecnica').value = tecnica || '';
                document.getElementById('editar_ano').value = ano || '';
                document.getElementById('editar_material').value = material || '';
                document.getElementById('editar_estoque').value = estoque;
                document.getElementById('editar_destaque').checked = destaque == 1;
                document.getElementById('editar_imagem_atual').value = imagem || '';

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

                // Processar categorias
                try {
                    const categoriasArray = JSON.parse(categorias);
                    console.log('Categorias encontradas:', categoriasArray);
                    
                    // Limpar todas as seleções primeiro
                    document.querySelectorAll('#formEditar input[name="categorias[]"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    
                    // Marcar as categorias que existem
                    if (Array.isArray(categoriasArray)) {
                        categoriasArray.forEach(categoria => {
                            const checkbox = document.querySelector(`#formEditar input[name="categorias[]"][value="${categoria}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });
                    }
                } catch (e) {
                    console.error('Erro ao processar categorias:', e);
                    // Desmarcar todas as categorias em caso de erro
                    document.querySelectorAll('#formEditar input[name="categorias[]"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                }

                // Resetar nome do arquivo
                document.getElementById('nome_arquivo_editar').textContent = 'Manter imagem atual';

                // Mostrar o modal
                const modal = document.getElementById('modalEditar');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                console.log('Modal de edição aberto com sucesso');
            } catch (error) {
                console.error('Erro ao abrir modal de edição:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível abrir o formulário de edição.',
                    timer: 3000
                });
            }
        }

        // Função para fechar modal de edição
        function fecharModalEditar() {
            console.log('Fechando modal de edição');
            document.getElementById('modalEditar').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Função para abrir modal de nova obra
        function abrirModalNovaObra() {
            console.log('Abrindo modal de nova obra');
            try {
                document.getElementById('modalNovaObra').style.display = 'block';
                // Limpar formulário
                document.getElementById('formNovaObra').reset();
                // Resetar preview
                document.getElementById('preview_nova_imagem').style.display = 'none';
                document.getElementById('sem_preview_novo').style.display = 'flex';
                document.getElementById('nome_arquivo_novo').textContent = 'Nenhum arquivo selecionado';
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Erro ao abrir modal nova obra:', error);
            }
        }

        // Função para fechar modal de nova obra
        function fecharModalNovaObra() {
            console.log('Fechando modal de nova obra');
            document.getElementById('modalNovaObra').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Função para excluir obra
        function excluirObra(id, nome) {
            Swal.fire({
                title: 'Excluir Obra',
                html: `Tem certeza que deseja excluir a obra <strong>"${nome}"</strong>?<br><small>Esta ação não pode ser desfeita.</small>`,
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
                    document.getElementById('produtoExcluirId').value = id;
                    document.getElementById('formExcluir').submit();
                }
            });

            return false;
        }

        // Preview de imagem para edição
        document.getElementById('editar_imagem_url').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const previewImg = document.getElementById('preview_imagem');
            const semPreview = document.getElementById('sem_preview');
            const nomeArquivo = document.getElementById('nome_arquivo_editar');

            if (file) {
                nomeArquivo.textContent = file.name;

                // Verificar tamanho do arquivo
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Arquivo muito grande',
                        text: 'A imagem deve ter no máximo 5MB.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    this.value = '';
                    nomeArquivo.textContent = 'Manter imagem atual';
                    return;
                }

                // Verificar tipo do arquivo
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Formato não suportado',
                        text: 'Use apenas imagens JPG, PNG, GIF ou WEBP.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    this.value = '';
                    nomeArquivo.textContent = 'Manter imagem atual';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    semPreview.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                nomeArquivo.textContent = 'Manter imagem atual';
            }
        });

        // Preview de imagem para nova obra
        document.getElementById('nova_imagem_url').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const previewImg = document.getElementById('preview_nova_imagem');
            const semPreview = document.getElementById('sem_preview_novo');
            const nomeArquivo = document.getElementById('nome_arquivo_novo');

            if (file) {
                nomeArquivo.textContent = file.name;

                // Verificar tamanho do arquivo
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Arquivo muito grande',
                        text: 'A imagem deve ter no máximo 5MB.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    this.value = '';
                    nomeArquivo.textContent = 'Nenhum arquivo selecionado';
                    previewImg.style.display = 'none';
                    semPreview.style.display = 'flex';
                    return;
                }

                // Verificar tipo do arquivo
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Formato não suportado',
                        text: 'Use apenas imagens JPG, PNG, GIF ou WEBP.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    this.value = '';
                    nomeArquivo.textContent = 'Nenhum arquivo selecionado';
                    previewImg.style.display = 'none';
                    semPreview.style.display = 'flex';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    semPreview.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = 'none';
                semPreview.style.display = 'flex';
                nomeArquivo.textContent = 'Nenhum arquivo selecionado';
            }
        });

        // Fechar modais ao clicar fora ou pressionar ESC
        window.addEventListener('click', function (event) {
            const modalEditar = document.getElementById('modalEditar');
            const modalNova = document.getElementById('modalNovaObra');

            if (event.target === modalEditar) {
                fecharModalEditar();
            }
            if (event.target === modalNova) {
                fecharModalNovaObra();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                fecharModalEditar();
                fecharModalNovaObra();
            }
        });

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
                title: 'Obra excluída!',
                text: 'A obra foi excluída com sucesso.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['editado']) && $_GET['editado'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Obra atualizada!',
                text: 'As alterações foram salvas com sucesso.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['nova_sucesso']) && $_GET['nova_sucesso'] == 1): ?>
            Swal.fire({
                icon: 'success',
                title: 'Obra cadastrada!',
                html: 'Obra <strong><?php echo isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : ''; ?></strong> cadastrada com sucesso!',
                timer: 3000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] == 1): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Ocorreu um erro ao atualizar a obra.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['nova_erro']) && $_GET['nova_erro'] == 1): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Ocorreu um erro ao cadastrar a obra.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['erro_exclusao']) && $_GET['erro_exclusao'] == 1): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Ocorreu um erro ao excluir a obra.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff'
            });
        <?php endif; ?>

        // Inicialização quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página de obras carregada - Modais prontos para uso');
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

        tr:hover td {
            background: #fff6f5;
        }

        /* ===== IMAGEM DA OBRA ===== */
        .obra-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
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
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
            position: relative;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #db6d56, #a7503e);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            background: none;
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close:hover {
            color: #ffe8e2;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .modal-content form {
            padding: 25px;
        }

        /* Layout de colunas para formulário */
        .form-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .campo {
            margin-bottom: 15px;
        }

        .campo label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .campo input,
        .campo textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .campo input:focus,
        .campo textarea:focus {
            border-color: #db6d56;
            outline: none;
        }

        .campo small {
            color: #888;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }

        .categorias-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }

        .categorias-container label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
            margin-bottom: 0;
            cursor: pointer;
        }

        .categorias-container input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .preview-imagem {
            text-align: center;
            margin: 15px 0;
        }

        .preview-imagem img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #db6d56;
            object-fit: cover;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
            position: sticky;
            bottom: 0;
            background: white;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }

        .btn-cancelar {
            background: #95a5a6;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            font-size: 1rem;
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
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }

        .btn-salvar:hover {
            background: #a7503e;
        }

        /* Estilos para seleção de arquivo */
        .file-input-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .btn-selecionar-imagem {
            background: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-selecionar-imagem:hover {
            background: #2980b9;
        }

        .nome-arquivo {
            color: #666;
            font-size: 0.9rem;
            font-style: italic;
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

            .obra-img {
                width: 40px;
                height: 40px;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .form-columns {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .file-input-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</body>

</html>