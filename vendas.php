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

// Verifica se há um pedido de exclusão
if (isset($_GET['excluir'])) {
    $id = filter_input(INPUT_GET, 'excluir', FILTER_SANITIZE_NUMBER_INT);

    if ($id && isset($_SESSION['carrinho'][$id])) {
        // Devolve a quantidade ao estoque
        $produtoIndex = array_search($id, array_column($produtos, 'id'));
        if ($produtoIndex !== false) {
            $produtos[$produtoIndex]['estoque'] += $_SESSION['carrinho'][$id];
        }

        // Exclui o item do carrinho
        unset($_SESSION['carrinho'][$id]);

        header('Location: vendas.php');
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $produtoId = $_POST['produto'];
    $quantidade = $_POST['quantidade'];

    // Busca o índice do produto no array
    $produtoIndex = array_search($produtoId, array_column($produtos, 'id'));

    if ($produtoIndex !== false && $produtos[$produtoIndex]['estoque'] >= $quantidade) {
        if (isset($_SESSION['carrinho'][$produtoId])) {
            $_SESSION['carrinho'][$produtoId] += $quantidade;
        } else {
            $_SESSION['carrinho'][$produtoId] = $quantidade;
        }

        $produtos[$produtoIndex]['estoque'] -= $quantidade;
    } else {
        echo "Estoque insuficiente para o Produto {$produtoId}.";
    }
}

include_once("./layout/_header.php");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas de Produtos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Inclua o Font Awesome para o ícone de carrinho -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body class="bg-dark">

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar bg-dark mt-4">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <h3 class="text-light">Menu</h3>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="movimentacoes.php">
                                <h5 class="text-light">Controle de Estoque</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="vendas.php">
                                <h5 class="text-light">Vendas Produtos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <h5 class="text-light">Cadastro Produto</h5>
                            </a>
                        </li>

                    </ul>

                </div>

            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <h1 class="text-center mb-4">Vendas de Produtos</h1>
                    </div>
                    <div class="card-body">
                        <h2 class="text-center mb-4">Produtos Disponíveis</h2>
                        <table class="table table-bordered mt-5">
                            <!-- ... Restante do código permanece o mesmo ... -->
                        </table>
                        <h2 class="text-center mb-4">Carrinho de Compras</h2>
                        <table class="table table-bordered mt-5">
                            <!-- ... Restante do código permanece o mesmo ... -->
                        </table>
                    </div>
                    <div class="card-footer">
                        <!-- Conteúdo do rodapé do card -->
                    </div>
                </div>
            </main>
        </div>
    </div>


    <!-- Carrinho de Compras - Modal -->
    <div class="modal fade" id="carrinhoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Carrinho de Compras</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Conteúdo do Carrinho de Compras -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nome do Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['carrinho'] as $produtoId => $quantidade) : ?>
                                <?php $produtoIndex = $produtoId - 1; ?>
                                <?php if (isset($produtos[$produtoIndex])) : ?>
                                    <?php $produto = $produtos[$produtoIndex]; ?>
                                    <tr>
                                        <td><?= $produto['id'] ?></td>
                                        <td><?= $produto['nome'] ?></td>
                                        <td><?= $quantidade ?></td>
                                        <td>R$<?= $produto['preco'] ?></td>
                                        <td>R$<?= $quantidade * $produto['preco'] ?></td>
                                        <td>
                                            <a href="vendas.php?excluir=<?= $produto['id'] ?>" class="btn btn-sm btn-danger">Remover</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Código JavaScript para ativar o modal -->
    <script>
        $(document).ready(function() {
            $('#carrinhoModal').on('show.bs.modal', function() {
                // Ações a serem executadas quando o modal é exibido
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>