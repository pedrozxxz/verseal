<?php
session_start();

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Garante que o carrinho exista
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meio_pagamento = $_POST['meio_pagamento'] ?? '';

    // VALIDAÇÃO DOS CAMPOS DO CARTÃO
    if ($meio_pagamento === 'cartao') {
        $campos_obrigatorios = ['numero_cartao', 'nome_cartao', 'validade', 'cvv'];
        foreach ($campos_obrigatorios as $campo) {
            if (empty($_POST[$campo])) {
                $_SESSION['erro_checkout'] = "Preencha todos os dados do cartão de crédito";
                header('Location: checkout.php');
                exit;
            }
        }
        
        // Validação básica do cartão
        if (strlen(str_replace([' ', '-'], '', $_POST['numero_cartao'])) < 13) {
            $_SESSION['erro_checkout'] = "Número do cartão inválido";
            header('Location: checkout.php');
            exit;
        }
    }

    $carrinho = $_SESSION['carrinho'] ?? [];
    $valor_total = 0;

    if (!empty($carrinho) && is_array($carrinho)) {
        $valor_total = array_sum(array_map(fn($item) => $item["preco"], $carrinho));
    }

    // VERIFICAÇÃO DO USUÁRIO LOGADO
    $usuario_id = null;
    if (isset($_SESSION["clientes"]) && is_array($_SESSION["clientes"])) {
        $usuario_id = $_SESSION["clientes"]['id'] ?? null;
    } elseif (isset($_SESSION["artistas"]) && is_array($_SESSION["artistas"])) {
        $usuario_id = $_SESSION["artistas"]['id'] ?? null;
    }

    // Salvar dados do pedido na sessão
    $_SESSION['dados_pedido'] = [
        'cliente' => [
            'nome' => $_POST['nome_completo'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telefone' => $_POST['telefone'] ?? ''
        ],
        'endereco' => [
            'cep' => $_POST['cep'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'cidade' => $_POST['cidade'] ?? '',
            'bairro' => $_POST['bairro'] ?? '',
            'endereco' => $_POST['endereco'] ?? '',
            'numero' => $_POST['numero'] ?? '',
            'complemento' => $_POST['complemento'] ?? ''
        ],
        'pagamento' => [
            'metodo' => $meio_pagamento,
            'valor_total' => $valor_total,
            'dados_cartao' => $meio_pagamento === 'cartao' ? [
                'numero' => $_POST['numero_cartao'] ?? '',
                'nome' => $_POST['nome_cartao'] ?? '',
                'validade' => $_POST['validade'] ?? '',
                'cvv' => $_POST['cvv'] ?? '',
                'parcelas' => $_POST['parcelas'] ?? 1
            ] : []
        ],
        'itens' => $carrinho,
        'usuario_id' => $usuario_id
    ];

    // REGISTRAR PEDIDO NO BANCO
    $pedido_id = 'VS' . date('YmdHis') . rand(100, 999);
    $status_pedido = $meio_pagamento === 'cartao' ? 'pago' : 'aguardando_pagamento';
    
    try {
        $conn->begin_transaction();
        
        // 1. Inserir pedido - MESMA QUERY PARA TODOS OS MÉTODOS
        if ($status_pedido === 'pago') {
            // Se for cartão, já marca como pago com data de pagamento
            $sql_pedido = "INSERT INTO pedidos (usuario_id, codigo_pedido, valor_total, status, metodo_pagamento, data_pedido, data_pagamento) 
                          VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        } else {
            // Se for PIX ou Boleto, marca como aguardando sem data de pagamento
            $sql_pedido = "INSERT INTO pedidos (usuario_id, codigo_pedido, valor_total, status, metodo_pagamento, data_pedido) 
                          VALUES (?, ?, ?, ?, ?, NOW())";
        }
        
        $stmt_pedido = $conn->prepare($sql_pedido);
        
        // CORREÇÃO: MESMO NÚMERO DE PARÂMETROS PARA AMBOS OS CASOS
        if ($status_pedido === 'pago') {
            // Para cartão: 5 parâmetros
            $stmt_pedido->bind_param("isdss", 
                $usuario_id,
                $pedido_id,
                $valor_total,
                $status_pedido,
                $meio_pagamento
            );
        } else {
            // Para PIX/Boleto: 5 parâmetros TAMBÉM
            $stmt_pedido->bind_param("isdss", 
                $usuario_id,
                $pedido_id,
                $valor_total,
                $status_pedido,
                $meio_pagamento
            );
        }
        
        if (!$stmt_pedido->execute()) {
            throw new Exception("Erro ao inserir pedido: " . $stmt_pedido->error);
        }
        
        $pedido_db_id = $conn->insert_id;
        $stmt_pedido->close();
        
        // 2. Inserir endereço de entrega
        $sql_endereco = "INSERT INTO enderecos_entrega (pedido_id, nome_destinatario, cep, logradouro, numero, complemento, bairro, cidade, estado, telefone) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_endereco = $conn->prepare($sql_endereco);
        
        // Garantir valores padrão
        $nome_completo = $_POST['nome_completo'] ?? '';
        $cep = $_POST['cep'] ?? '';
        $endereco = $_POST['endereco'] ?? '';
        $numero = $_POST['numero'] ?? '';
        $complemento = $_POST['complemento'] ?? '';
        $bairro = $_POST['bairro'] ?? '';
        $cidade = $_POST['cidade'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        
        $stmt_endereco->bind_param("isssssssss",
            $pedido_db_id,
            $nome_completo,
            $cep,
            $endereco,
            $numero,
            $complemento,
            $bairro,
            $cidade,
            $estado,
            $telefone
        );
        
        if (!$stmt_endereco->execute()) {
            throw new Exception("Erro ao inserir endereço: " . $stmt_endereco->error);
        }
        $stmt_endereco->close();
        
        // 3. Atualizar vendas mensais (apenas se o pedido for pago)
        if ($status_pedido === 'pago') {
            $mes = date('m');
            $ano = date('Y');
            
            $sql_check_venda = "SELECT id, valor_total FROM vendas WHERE mes = ? AND ano = ?";
            $stmt_check = $conn->prepare($sql_check_venda);
            $stmt_check->bind_param("ii", $mes, $ano);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                $venda_existente = $result_check->fetch_assoc();
                $novo_valor = $venda_existente['valor_total'] + $valor_total;
                
                $sql_update_venda = "UPDATE vendas SET valor_total = ?, data_registro = NOW() WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update_venda);
                $stmt_update->bind_param("di", $novo_valor, $venda_existente['id']);
                
                if (!$stmt_update->execute()) {
                    throw new Exception("Erro ao atualizar venda: " . $stmt_update->error);
                }
                $stmt_update->close();
            } else {
                $sql_insert_venda = "INSERT INTO vendas (mes, ano, valor_total, data_registro) VALUES (?, ?, ?, NOW())";
                $stmt_insert = $conn->prepare($sql_insert_venda);
                $stmt_insert->bind_param("iid", $mes, $ano, $valor_total);
                
                if (!$stmt_insert->execute()) {
                    throw new Exception("Erro ao inserir venda: " . $stmt_insert->error);
                }
                $stmt_insert->close();
            }
            $stmt_check->close();
        }
        
        $conn->commit();
        
        // Salvar ID do pedido na sessão
        $_SESSION['ultimo_pedido'] = [
            'id' => $pedido_id,
            'id_db' => $pedido_db_id,
            'valor' => $valor_total,
            'cliente' => $_POST['nome_completo'] ?? '',
            'metodo' => $meio_pagamento,
            'status' => $status_pedido,
            'data' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro ao registrar pedido: " . $e->getMessage());
        $_SESSION['erro_checkout'] = "Erro ao processar pedido. Tente novamente.";
        header('Location: checkout.php');
        exit;
    }

    // Limpar carrinho após sucesso
    unset($_SESSION['carrinho']);

    // Redirecionamentos
    if ($meio_pagamento === 'pix') {
        header('Location: processar-pix.php');
        exit;
    } elseif ($meio_pagamento === 'boleto') {
        header('Location: processar-boleto.php');
        exit;
    } elseif ($meio_pagamento === 'cartao') {
        header('Location: sucesso-compra.php');
        exit;
    }
}

$conn->close();
header('Location: carrinho.php');
exit;
?>