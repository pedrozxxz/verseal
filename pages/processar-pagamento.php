<?php
session_start();

// Garante que o carrinho exista
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meio_pagamento = $_POST['meio_pagamento'] ?? '';

    $carrinho = $_SESSION['carrinho'] ?? [];
    $valor_total = 0;

    if (!empty($carrinho) && is_array($carrinho)) {
        $valor_total = array_sum(array_map(fn($item) => $item["preco"] * $item["qtd"], $carrinho));
    }

    $_SESSION['dados_pedido'] = [
        'cliente' => [
            'nome' => $_POST['nome_completo'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone']
        ],
        'endereco' => [
            'cep' => $_POST['cep'],
            'estado' => $_POST['estado'],
            'cidade' => $_POST['cidade'],
            'bairro' => $_POST['bairro'],
            'endereco' => $_POST['endereco'],
            'numero' => $_POST['numero'],
            'complemento' => $_POST['complemento'] ?? ''
        ],
        'pagamento' => [
            'metodo' => $meio_pagamento,
            'valor_total' => $valor_total
        ]
    ];

    // Redirecionamentos
    if ($meio_pagamento === 'pix') {
        header('Location: processar-pix.php');
        exit;
    } elseif ($meio_pagamento === 'boleto') {
        header('Location: processar-boleto.php');
        exit;
    } elseif ($meio_pagamento === 'cartao') {
        unset($_SESSION['carrinho']);
        header('Location: sucesso-compra.php');
        exit;
    }
}

header('Location: carrinho.php');
exit;
?>