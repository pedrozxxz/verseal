<?php
session_start();

// VERIFICAÇÃO CORRIGIDA DA SESSÃO
$usuarioLogado = null;
if (isset($_SESSION["clientes"])) {
    $usuarioLogado = $_SESSION["clientes"];
    $tipoUsuario = "cliente";
} elseif (isset($_SESSION["artistas"])) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
} elseif (isset($_SESSION["usuario"])) {
    $usuarioLogado = $_SESSION["usuario"];
    $tipoUsuario = "usuario";
}

// Verificar se o carrinho está vazio
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit;
}

// Verificar se o usuário está logado
if (!$usuarioLogado) {
    header('Location: login.php?from=checkout');
    exit;
}

$carrinho = $_SESSION['carrinho'];
// CALCULAR TOTAL SEM QUANTIDADE (cada item é único)
$total = array_sum(array_map(fn($item) => $item["preco"], $carrinho));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verseal - Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/checkout.css">
</head>

<body>
    <!-- HEADER -->
    <header>
        <div class="logo">Verseal</div>
        <nav>
            <a href="../index.php">Início</a>
            <a href="./produto.php">Obras</a>
            <a href="./sobre.php">Sobre</a>
            <a href="./artistas.php">Artistas</a>
            <a href="./contato.php">Contato</a>
            <a href="./carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
            
            <div class="profile-dropdown">
                <a href="#" class="icon-link" id="profile-icon"><i class="fas fa-user"></i></a>
                <div class="dropdown-content" id="profile-dropdown">
                    <?php if ($usuarioLogado): ?>
                        <div class="user-info">
                            <p>Seja bem-vindo, 
                                <span id="user-name">
                                    <?php 
                                    if (is_array($usuarioLogado)) {
                                        echo htmlspecialchars($usuarioLogado['nome'] ?? $usuarioLogado['nome_artistico'] ?? 'Usuário');
                                    } else {
                                        echo htmlspecialchars($usuarioLogado);
                                    }
                                    ?>
                                </span>!
                            </p>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="./perfil.php" class="dropdown-item"><i class="fas fa-user-circle"></i> Meu Perfil</a>
                        <a href="./minhas-compras.php" class="dropdown-item"><i class="fas fa-shopping-bag"></i> Minhas Compras</a>
                        <div class="dropdown-divider"></div>
                        <a href="./logout.php" class="dropdown-item logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    <?php else: ?>
                        <div class="user-info">
                            <p>Faça login para acessar seu perfil</p>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="./login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
                        <a href="./login.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Cadastrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- CONTEÚDO -->
    <main class="pagina-checkout">
        <div class="checkout-container">
            <!-- RESUMO DO PEDIDO -->
            <div class="resumo-pedido">
                <h2>Resumo do Pedido</h2>
                <div class="itens-pedido">
                    <?php foreach ($carrinho as $item): ?>
                        <div class="item-pedido">
                            <img src="<?php echo $item['img']; ?>" alt="<?php echo $item['nome']; ?>">
                            <div class="item-info">
                                <h4><?php echo $item['nome']; ?></h4>
                                <span class="preco-item">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="total-pedido">
                    <div class="linha-total">
                        <span>Subtotal:</span>
                        <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    <div class="linha-total">
                        <span>Frete:</span>
                        <span>Grátis</span>
                    </div>
                    <div class="linha-total total-final">
                        <strong>Total:</strong>
                        <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>

            <!-- FORMULÁRIO DE PAGAMENTO -->
            <div class="formulario-pagamento">
                <h1>Verseal</h1>
                <div class="divisor"></div>

                <!-- INFO DO USUÁRIO LOGADO -->
                <div class="usuario-info">
                    <div class="usuario-logado">
                        <i class="fas fa-user-check"></i>
                        <div class="usuario-detalhes">
                            <strong>Olá, 
                                <?php 
                                if (is_array($usuarioLogado)) {
                                    echo htmlspecialchars($usuarioLogado['nome'] ?? $usuarioLogado['nome_artistico'] ?? 'Usuário');
                                } else {
                                    echo htmlspecialchars($usuarioLogado);
                                }
                                ?>!
                            </strong>
                            <span>Você está logado e pode finalizar sua compra</span>
                        </div>
                    </div>
                </div>

                <form id="form-checkout" method="POST" action="processar-pagamento.php">
                    <!-- DADOS PESSOAIS -->
                    <div class="secao-form">
                        <h3>Dados Pessoais</h3>
                        <div class="campo-grupo">
                            <div class="campo">
                                <label>Nome completo *</label>
                                <input type="text" name="nome_completo" required placeholder="Digite seu nome completo">
                            </div>
                        </div>

                        <div class="campo-grupo">
                            <div class="campo">
                                <label>Email *</label>
                                <input type="email" name="email" required placeholder="Digite seu email">
                            </div>
                            <div class="campo">
                                <label>Telefone *</label>
                                <input type="tel" name="telefone" required placeholder="Digite seu telefone">
                            </div>
                        </div>
                    </div>

                    <!-- ENDEREÇO -->
                    <div class="secao-form">
                        <h3>Endereço de Entrega</h3>
                        <div class="campo-grupo">
                            <div class="campo">
                                <label>CEP *</label>
                                <input type="text" name="cep" required placeholder="Digite o CEP">
                            </div>
                            <div class="campo">
                                <label>Estado *</label>
                                <select name="estado" required>
                                    <option value="">Selecione...</option>
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>
                        </div>

                        <div class="campo-grupo">
                            <div class="campo">
                                <label>Cidade *</label>
                                <input type="text" name="cidade" required placeholder="Digite a cidade">
                            </div>
                            <div class="campo">
                                <label>Bairro *</label>
                                <input type="text" name="bairro" required placeholder="Digite o bairro">
                            </div>
                        </div>

                        <div class="campo-grupo">
                            <div class="campo">
                                <label>Endereço *</label>
                                <input type="text" name="endereco" required placeholder="Digite o endereço">
                            </div>
                            <div class="campo">
                                <label>Número *</label>
                                <input type="text" name="numero" required placeholder="Número">
                            </div>
                        </div>

                        <div class="campo">
                            <label>Complemento</label>
                            <input type="text" name="complemento" placeholder="Complemento (opcional)">
                        </div>
                    </div>

                    <!-- PAGAMENTO -->
                    <div class="secao-form">
                        <h3>Pagamento</h3>

                        <div class="campo-grupo">
                            <div class="campo">
                                <label>Meio de pagamento *</label>
                                <select name="meio_pagamento" id="meio-pagamento" required>
                                    <option value="">Selecione...</option>
                                    <option value="cartao">Cartão de Crédito</option>
                                    <option value="pix">PIX</option>
                                    <option value="boleto">Boleto Bancário</option>
                                </select>
                            </div>
                        </div>

                        <!-- CARTÃO DE CRÉDITO -->
                        <div id="dados-cartao" class="dados-pagamento">
                            <div class="campo-grupo">
                                <div class="campo">
                                    <label>Número do cartão *</label>
                                    <input type="text" name="numero_cartao" placeholder="Digite o número do cartão" maxlength="19">
                                </div>
                            </div>

                            <div class="campo-grupo">
                                <div class="campo">
                                    <label>Nome no cartão *</label>
                                    <input type="text" name="nome_cartao" placeholder="Digite o nome no cartão">
                                </div>
                            </div>

                            <div class="campo-grupo">
                                <div class="campo">
                                    <label>Validade *</label>
                                    <input type="text" name="validade" placeholder="MM/AA" maxlength="5">
                                </div>
                                <div class="campo">
                                    <label>CVV *</label>
                                    <input type="text" name="cvv" placeholder="CVV" maxlength="4">
                                </div>
                            </div>

                            <div class="campo-grupo">
                                <div class="campo">
                                    <label>Parcelas *</label>
                                    <select name="parcelas">
                                        <option value="1">1x R$ <?php echo number_format($total, 2, ',', '.'); ?></option>
                                        <option value="2">2x R$ <?php echo number_format($total / 2, 2, ',', '.'); ?></option>
                                        <option value="3">3x R$ <?php echo number_format($total / 3, 2, ',', '.'); ?></option>
                                        <option value="4">4x R$ <?php echo number_format($total / 4, 2, ',', '.'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- PIX -->
                        <div id="dados-pix" class="dados-pagamento" style="display: none;">
                            <div class="info-pix">
                                <p>⚠️ Ao confirmar o pedido, geraremos um QR Code PIX para pagamento.</p>
                                <p>O prazo para pagamento é de 30 minutos.</p>
                            </div>
                        </div>

                        <!-- BOLETO -->
                        <div id="dados-boleto" class="dados-pagamento" style="display: none;">
                            <div class="info-boleto">
                                <p>⚠️ Ao confirmar o pedido, geraremos um boleto bancário.</p>
                                <p>O prazo para pagamento é de 3 dias úteis.</p>
                            </div>
                        </div>
                    </div>

                    <div class="divisor"></div>

                    <button type="submit" class="btn-enviar">
                        <i class="fas fa-lock"></i> Finalizar Compra
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2025 Verseal. Todos os direitos reservados.</p>
        <div class="social">
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-whatsapp"></i></a>
        </div>
    </footer>

    <script>
        // JavaScript permanece igual...
        // Mostrar/ocultar campos de pagamento baseado na seleção
        const meioPagamento = document.getElementById('meio-pagamento');
        const dadosCartao = document.getElementById('dados-cartao');
        const dadosPix = document.getElementById('dados-pix');
        const dadosBoleto = document.getElementById('dados-boleto');

        meioPagamento.addEventListener('change', function () {
            dadosCartao.style.display = 'none';
            dadosPix.style.display = 'none';
            dadosBoleto.style.display = 'none';

            switch (this.value) {
                case 'cartao':
                    dadosCartao.style.display = 'block';
                    break;
                case 'pix':
                    dadosPix.style.display = 'block';
                    break;
                case 'boleto':
                    dadosBoleto.style.display = 'block';
                    break;
            }
        });

        // ... resto do JavaScript permanece igual
    </script>
</body>
</html>