<?php
require_once('conexao.php');

// Inicializa a sessão
session_start();

// Buscar produtos no banco de dados
$stmt = $conn->query('SELECT * FROM cadastrar_produto');
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$resultadoPesquisa = ""; // Variável para armazenar o resultado da pesquisa

// Verifica se há uma solicitação de pesquisa por ID
if (isset($_GET['produtoId'])) {
    $produtoId = filter_input(INPUT_GET, 'produtoId', FILTER_SANITIZE_NUMBER_INT);

    // Busca o índice do produto no array
    $produtoIndex = array_search($produtoId, array_column($produtos, 'id'));

    if ($produtoIndex !== false) {
        // Exibe as informações do produto encontrado
        $produto = $produtos[$produtoIndex];
        $resultadoPesquisa = "Produto: {$produto['produto']}  Total Estoque: {$produto['quantidade']}";
    } else {
        $resultadoPesquisa = "Produto não encontrado.";
    }
}

// Verifica se há uma solicitação para remover um produto específico do carrinho
if (isset($_GET['excluir'])) {
    $produtoExcluirId = filter_input(INPUT_GET, 'excluir', FILTER_SANITIZE_NUMBER_INT);

    // Remove o produto do carrinho
    if (isset($_SESSION['carrinho'][$produtoExcluirId])) {
        // Adiciona a quantidade de volta ao estoque
        $produtos[$produtoExcluirId]['quantidade'] += $_SESSION['carrinho'][$produtoExcluirId];

        // Remove o produto do carrinho
        unset($_SESSION['carrinho'][$produtoExcluirId]);
    }
}

// Verifica se há uma solicitação para excluir todos os produtos do carrinho
if (isset($_GET['excluirTodos'])) {
    // Remove todos os produtos do carrinho e adiciona a quantidade de volta ao estoque
    foreach ($_SESSION['carrinho'] as $produtoId => $quantidade) {
        $produtos[$produtoId]['quantidade'] += $quantidade;
    }
    // Limpa o carrinho
    $_SESSION['carrinho'] = [];
}

// Adicionar produto ao carrinho
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['produtoId']) && isset($_POST['quantidade'])) {
    $produtoId = filter_input(INPUT_POST, 'produtoId', FILTER_SANITIZE_NUMBER_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_SANITIZE_NUMBER_INT);


    // Verifica se o produto existe no array de produtos
    if (isset($produtos[$produtoId])) {
        $produto = $produtos[$produtoId];

        // Verifica se há estoque suficiente
        if ($produto['quantidade'] >= $quantidade) {
            // Adiciona o produto ao carrinho
            if (isset($_SESSION['carrinho'][$produtoId])) {
                $_SESSION['carrinho'][$produtoId] += $quantidade;
            } else {
                $_SESSION['carrinho'][$produtoId] = $quantidade;
            }


            // Atualiza a quantidade disponível do produto
            $produtos[$produtoId]['quantidade'] -= $quantidade;
        } else {
            echo "Estoque insuficiente para o Produto {$produtoId}.";
        }
    } else {
        echo "Produto com ID {$produtoId} não encontrado.";
    }
}

// Remover produto do carrinho
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['removerProdutoId'])) {
    $produtoId = filter_input(INPUT_POST, 'removerProdutoId', FILTER_SANITIZE_NUMBER_INT);

    // Remove o produto do carrinho
    if (isset($_SESSION['carrinho'][$produtoId])) {
        // Adiciona a quantidade de volta ao estoque
        $produtos[$produtoId]['quantidade'] += $_SESSION['carrinho'][$produtoId];

        // Remove o produto do carrinho
        unset($_SESSION['carrinho'][$produtoId]);
    }
}


// Limpar carrinho
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['limparCarrinho'])) {
    // Remove todos os produtos do carrinho e adiciona a quantidade de volta ao estoque
    foreach ($_SESSION['carrinho'] as $produtoId => $quantidade) {
        $produtos[$produtoId]['quantidade'] += $quantidade;
    }
    // Limpa o carrinho
    $_SESSION['carrinho'] = [];
}

// Adiciona produtos ao carrinho quando o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $totalVenda = 0; // Variável para armazenar o total da venda
    // Verifica se o array $_POST['produto'] está definido e é um array
    if (isset($_POST['produto']) && is_array($_POST['produto'])) {
        foreach ($_POST['produto'] as $produtoId => $quantidade) {
            // Verifica se o produto existe no array de produtos
            if (isset($produtos[$produtoId])) {
                $produto = $produtos[$produtoId];

                // Verifica se há estoque suficiente
                if ($produto['quantidade'] >= $quantidade) {
                    // Adiciona o produto ao carrinho
                    if (isset($_SESSION['carrinho'][$produtoId])) {
                        $_SESSION['carrinho'][$produtoId] += $quantidade;
                    } else {
                        $_SESSION['carrinho'][$produtoId] = $quantidade;
                    }

                    // Atualiza a quantidade disponível do produto
                    $produtos[$produtoId]['quantidade'] -= $quantidade;

                    // Atualiza o total da venda
                    $totalVenda += $quantidade * $produto['valor_unitario'];
                } else {
                    echo "Estoque insuficiente para o Produto {$produtoId}.";
                }
            } else {
                echo "Produto com ID {$produtoId} não encontrado.";
            }
        }
    } else {
        echo "Nenhum produto foi enviado via formulário.";
    }

    // Insere a venda na tabela de vendas
    if ($totalVenda > 0) {
        try {
            $conn->beginTransaction();

            foreach ($_SESSION['carrinho'] as $produtoId => $quantidade) {
                // Certifique-se de que o produto exista no array de produtos
                if (isset($produtos[$produtoId])) {
                    $produto = $produtos[$produtoId];
                    // Insert into irá buscar os valores dentro da tabela. atenção quanto ao nome da tabela 
                    $stmt = $conn->prepare('INSERT INTO vendas (produto_id, quantidade, valor_unitario) VALUES (?, ?, ?)');
                    $stmt->execute([$produtoId, $quantidade, $produto['valor_unitario']]);
                } else {
                    echo "Erro: Produto com ID {$produtoId} não encontrado.";
                }
            }

            $conn->commit();
            $_SESSION['carrinho'] = []; // Limpa o carrinho após a venda ser registrada
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Erro ao registrar a venda: " . $e->getMessage();
        }
    }
}




include_once("./layout/_header.php"); ?>



<body class="bg-dark">
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar bg-dark mt-4">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item ">
                            <h3 class="text-light">Menu</h3>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="index.php">
                                <h5 class="text-light">Cadastro Produtos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="movimentacoes.php">
                                <h5 class="text-light">Controle de Estoque</h5>
                            </a>
                        </li>


                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 ">
                <div class="card mt-4">
                    <div class="card-header  custom-card-header bg-warning">
                        <h1 class="text-center mb-4"> Produtos</h1>
                    </div>
                    <!--use o margin m-3 para  deixar uma borda branca top.--->
                    <div class="card-body bg-dark m-3">
                        <!-- Formulário de Pesquisa por ID -->
                        <form method="GET" action="vendas.php">
                            <div class="form-group">
                                <label for="produtoId" class="text-white">Pesquisar por ID do Produto:</label>
                                <input type="text" class="form-control mt-2" id="produtoId" name="produtoId" placeholder="Insira o Código do Produto">
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Pesquisar</button>
                        </form>

                        <!-- Exibição do resultado da pesquisa -->
                        <?php if ($resultadoPesquisa) : ?>
                            <p id="resultadoPesquisa" class="resultado-pesquisa text-white mt-3"><?= $resultadoPesquisa ?></p>
                        <?php endif; ?>

                        <h2 class="text-center   mb-5 text-white">Produtos Disponíveis</h2>
                        <form method="POST" action="vendas.php" id="carrinhoForm">
                            <table class="table table-striped table-dark ">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Produto</th>
                                        <th>Estoque</th>
                                        <th>Quantidade</th>
                                        <th>Adicionar ao Carrinho</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--Loop que exibe os produtos-->
                                    <?php foreach ($produtos as $produto) : ?>
                                        <tr>
                                            <td><?= $produto['id'] ?></td>
                                            <td><?= $produto['produto'] ?></td>
                                            <td><?= $produto['quantidade'] ?></td>
                                            <td>

                                                <input type="number" id="quantidade<?= $produto['id'] ?>" name="produto[<?= $produto['id'] ?>]" min="0" max="<?= $produto['quantidade'] ?>" value="0">
                                            </td>
                                            <td>
                                                <?php if ($produto['quantidade'] > 0) : ?>


                                                    <button type="button" class="btn btn-primary adicionar-ao-carrinho" data-idproduto="<?= $produto['id'] ?>" data-quantidade-maxima="<?= $produto['quantidade'] ?>">Adicionar</button>
                                                <?php else : ?>
                                                    <button type="button" class="btn btn-secondary" disabled>Indisponível</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </form>

                        <!-- Área do botão carrinho -->
                        <button type="button" class="btn btn-primary bg-primary" data-toggle="modal" data-target="#carrinhoModal">
                            <i class="fas fa-shopping-cart"></i> Carrinho
                        </button>
                        <!--Button de limpar carrinho -->
                        <!--<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmarRemoverTodosModal">Limpar Carrinho</button> -->
                        <button type="button" class="btn btn-danger" id="limparCarrinhoBtn">Limpar Carrinho</button>
                    </div>

                    <div class="card-footer">
                        <!-- Conteúdo do rodapé do card -->
                        <?php if (!empty($_SESSION['carrinho'])) : ?>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#carrinhoModal">
                                Finalizar Compra
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para exibir carrinho de compras -->
    <div class="modal fade" id="carrinhoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <!--CARRINHO-->
                    <h5 class="modal-title" id="exampleModalLabel">Carrinho de Compras</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($_SESSION['carrinho'])) : ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Preço Unitário</th>
                                    <th>Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['carrinho'] as $produtoId => $quantidade) : ?>
                                    <?php if (isset($produtos[$produtoId])) : ?>
                                        <?php $produto = $produtos[$produtoId]; ?>
                                        <tr>
                                            <td><?= $produto['id'] ?></td>
                                            <td><?= $produto['produto'] ?></td>
                                            <td><?= $quantidade ?></td>
                                            <td>R$<?= $produto['valor_unitario'] ?></td>
                                            <td>R$<?= $quantidade * $produto['valor_unitario'] ?></td>
                                            <td>
                                                <form method="POST" action="vendas.php">
                                                    <input type="hidden" name="removerProdutoId" value="<?= $produtoId ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="6">Produto não encontrado</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="alert alert-info" role="alert">
                            Carrinho vazio
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>




            <!-- Modal para confirmar a remoção de todos os itens do carrinho -->
            <div class="modal fade" id="confirmarRemoverTodosModal" tabindex="-1" role="dialog" aria-labelledby="confirmarRemoverTodosModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmarRemoverTodosModalLabel">Limpar Carrinho</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Tem certeza de que deseja remover todos os itens do carrinho?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <a href="vendas.php?excluirTodos=true" class="btn btn-danger" id="limparCarrinhoBtn">Limpar Carrinho</a>
                        </div>
                    </div>
                </div>
            </div>


            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


            <script>
                // Evento de clique nos botões "Adicionar ao Carrinho"
                $('.adicionar-ao-carrinho').on('click', function(event) {
                    event.preventDefault(); // Impede o comportamento padrão do link

                    var produtoId = $(this).data('idproduto');
                    var quantidadeMaxima = $(this).data('quantidade-maxima');
                    var quantidade = $('input[name="produto[' + produtoId + ']"]').val(); // Obtém a quantidade do produto

                    // Verifica se a quantidade é válida
                    if (quantidade <= 0 || quantidade > quantidadeMaxima) {
                        alert('Por favor, insira uma quantidade válida.');
                        return;
                    }

                    // Envia uma solicitação AJAX para adicionar o produto ao carrinho
                    $.post('adicionar_produto.php', {
                        'produtoId': produtoId,
                        'quantidade': quantidade
                    }, function(data) {
                        // Verifica se houve sucesso ao adicionar o produto
                        if (data.trim() !== '') {
                            // Atualiza o conteúdo do modal do carrinho após adicionar o item
                            $('.modal-body table tbody').append(data);
                        } else {
                            alert('Erro ao adicionar o produto ao carrinho.');
                        }
                    });
                });




                // Evento de clique nos botões "Remover do Carrinho"
                $(document).on('click', '.remover-do-carrinho', function(event) {
                    event.preventDefault(); // Impede o comportamento padrão do link

                    var produtoId = $(this).data('idproduto');

                    // Envia uma solicitação POST para excluir o produto específico do carrinho
                    $.post('vendas.php', {
                        'removerProdutoId': produtoId
                    }, function(data) {
                        // Atualiza o conteúdo do modal do carrinho após remover o item
                        $('.modal-body').html(data);
                    });
                });

                // Evento de clique no botão "Limpar Carrinho"
                $('#limparCarrinhoBtn').on('click', function(event) {
                    event.preventDefault(); // Impede o comportamento padrão do link

                    // Envia uma solicitação GET para limpar o carrinho
                    $.get('vendas.php?excluirTodos=true', function(data) {
                        // Recarrega a página após limpar o carrinho
                        location.reload();
                    });
                });
            </script>

</body>

</html>
