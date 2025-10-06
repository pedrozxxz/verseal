<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meio_pagamento = $_POST['meio_pagamento'] ?? '';
    
    // Salvar dados na sessão
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
            'valor_total' => array_sum(array_map(fn($item) => $item["preco"] * $item["qtd"], $_SESSION['carrinho']))
        ]
    ];
    
    // Redirecionar
    if ($meio_pagamento === 'pix') {
        header('Location: processar-pix.php');
        exit;
    } elseif ($meio_pagamento === 'boleto') {
        header('Location: processar-boleto.php');
        exit;
    } elseif ($meio_pagamento === 'cartao') {
        // Processar cartão
        unset($_SESSION['carrinho']);
        header('Location: sucesso-compra.php');
        exit;
    }
}

header('Location: carrinho.php');
exit;
?>