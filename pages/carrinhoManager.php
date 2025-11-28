<?php
// CarrinhoManager.php
class CarrinhoManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        session_start();
        
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
        
        if (!isset($_SESSION['carrinho_notificacoes'])) {
            $_SESSION['carrinho_notificacoes'] = [];
        }
    }
    
    // 🔹 SINCRONIZAR CARRINHO DA SESSÃO COM BANCO DE DADOS
    public function sincronizarCarrinho($usuario_id) {
        if (!$usuario_id) return false;
        
        // Buscar carrinho do banco para este usuário
        $carrinho_db = $this->buscarCarrinhoDB($usuario_id);
        
        // Se há itens na sessão e no banco, fazer merge
        if (!empty($_SESSION['carrinho']) && !empty($carrinho_db)) {
            $this->fazerMergeCarrinho($usuario_id, $carrinho_db);
        }
        // Se há itens na sessão mas usuário está logado agora, migrar para DB
        elseif (!empty($_SESSION['carrinho']) && empty($carrinho_db)) {
            $this->migrarSessaoParaDB($usuario_id);
        }
        // Se não há itens na sessão mas há no DB, carregar do DB
        elseif (empty($_SESSION['carrinho']) && !empty($carrinho_db)) {
            $this->carregarCarrinhoDoDB($carrinho_db);
        }
        
        return true;
    }
    
    // 🔹 BUSCAR CARRINHO DO BANCO
    private function buscarCarrinhoDB($usuario_id) {
        $sql = "SELECT c.*, p.nome, p.imagem_url, p.artista 
                FROM carrinho c 
                JOIN produtos p ON c.produto_id = p.id 
                WHERE c.usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $itens = [];
        while ($row = $result->fetch_assoc()) {
            $itens[] = $row;
        }
        return $itens;
    }
    
    // 🔹 FAZER MERGE ENTRE SESSÃO E BANCO
    private function fazerMergeCarrinho($usuario_id, $carrinho_db) {
        $itens_db = [];
        foreach ($carrinho_db as $item) {
            $itens_db[$item['produto_id']] = $item;
        }
        
        // Adicionar itens da sessão que não estão no DB
        foreach ($_SESSION['carrinho'] as $item_sessao) {
            if (!isset($itens_db[$item_sessao['id']])) {
                $this->adicionarItemDB($usuario_id, $item_sessao);
            }
        }
        
        // Atualizar sessão com dados do DB
        $this->carregarCarrinhoDoDB($carrinho_db);
    }
    
    // 🔹 MIGRAR SESSÃO PARA BANCO
    private function migrarSessaoParaDB($usuario_id) {
        foreach ($_SESSION['carrinho'] as $item) {
            $this->adicionarItemDB($usuario_id, $item);
        }
    }
    
    // 🔹 CARREGAR CARRINHO DO BANCO PARA SESSÃO
    private function carregarCarrinhoDoDB($carrinho_db) {
        $_SESSION['carrinho'] = [];
        foreach ($carrinho_db as $item) {
            $_SESSION['carrinho'][] = [
                'id' => $item['produto_id'],
                'img' => $this->processarImagemURL($item['imagem_url']),
                'nome' => $item['nome'],
                'preco' => (float)$item['preco_unitario'],
                'desc' => '', // Adicione outros campos se necessário
                'dimensao' => '',
                'quantidade' => $item['quantidade']
            ];
        }
    }
    
    // 🔹 ADICIONAR ITEM AO BANCO
    public function adicionarItemDB($usuario_id, $produto, $quantidade = 1) {
        // Verificar se item já existe no carrinho
        $sql = "SELECT id, quantidade FROM carrinho WHERE usuario_id = ? AND produto_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $produto['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Atualizar quantidade
            $item = $result->fetch_assoc();
            $nova_quantidade = $item['quantidade'] + $quantidade;
            
            $sql = "UPDATE carrinho SET quantidade = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $nova_quantidade, $item['id']);
        } else {
            // Inserir novo item
            $sql = "INSERT INTO carrinho (usuario_id, produto_id, quantidade, preco_unitario, data_adicao) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiid", $usuario_id, $produto['id'], $quantidade, $produto['preco']);
        }
        
        return $stmt->execute();
    }
    
    // 🔹 REMOVER ITEM DO BANCO
    public function removerItemDB($usuario_id, $produto_id) {
        $sql = "DELETE FROM carrinho WHERE usuario_id = ? AND produto_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $produto_id);
        return $stmt->execute();
    }
    
    // 🔹 LIMPAR CARRINHO NO BANCO
    public function limparCarrinhoDB($usuario_id) {
        $sql = "DELETE FROM carrinho WHERE usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        return $stmt->execute();
    }
    
    // 🔹 ATUALIZAR QUANTIDADE NO BANCO
    public function atualizarQuantidadeDB($usuario_id, $produto_id, $quantidade) {
        if ($quantidade <= 0) {
            return $this->removerItemDB($usuario_id, $produto_id);
        }
        
        $sql = "UPDATE carrinho SET quantidade = ?, updated_at = NOW() 
                WHERE usuario_id = ? AND produto_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $quantidade, $usuario_id, $produto_id);
        return $stmt->execute();
    }
    
    // 🔹 PROCESSAR URL DA IMAGEM (igual ao seu código atual)
    private function processarImagemURL($imagem_url) {
        if (empty($imagem_url)) {
            return '../img/imagem2.png';
        }
        
        if (strpos($imagem_url, '../') === 0) {
            return $imagem_url;
        } elseif (strpos($imagem_url, 'img/') === 0) {
            return '../' . $imagem_url;
        } elseif (strpos($imagem_url, 'uploads/') === 0) {
            return '../' . $imagem_url;
        } elseif (strpos($imagem_url, 'img/uploads/') === 0) {
            return '../' . $imagem_url;
        } else {
            return $imagem_url;
        }
    }
    
    // 🔹 ADICIONAR NOTIFICAÇÃO
    public function adicionarNotificacao($produto_id, $nome_produto) {
        $_SESSION['carrinho_notificacoes'][$produto_id] = [
            'nome' => $nome_produto,
            'timestamp' => time()
        ];
    }
    
    // 🔹 REMOVER NOTIFICAÇÃO
    public function removerNotificacao($produto_id) {
        if (isset($_SESSION['carrinho_notificacoes'][$produto_id])) {
            unset($_SESSION['carrinho_notificacoes'][$produto_id]);
        }
    }
    
    // 🔹 LIMPAR NOTIFICAÇÕES
    public function limparNotificacoes() {
        $_SESSION['carrinho_notificacoes'] = [];
    }
}
?>