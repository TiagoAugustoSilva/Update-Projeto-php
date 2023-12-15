<?php

require_once("conexao.php");

// Atenção: Este get está associado ao botão excluir, junto ao get global do código.
if (isset($_GET['excluir'])) {
    $id = filter_input(INPUT_GET, 'excluir', FILTER_SANITIZE_NUMBER_INT);

    if ($id) {
        $conn->exec('DELETE FROM cadastrar_produto WHERE id=' . $id);

        header('Location: index.php');
        exit;
    }
}

$results = $conn->query("select * from cadastrar_produto")->fetchAll();

$arrayDescricao = [1 => 'Eletrônicos', 2 => 'Informática', 3 => 'Domésticos', 4 => 'Celulares', 5 => 'Acessórios'];

include_once("./layout/_header.php");

?>

<div class="container-fluid">
    <div class="row">
        <!-- Menu Lateral -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar bg-dark mt-4">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <h3 class="text-light">Menu</h3>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link active" href="movimentacoes.php">
                            <h5 class="text-light"> Controle de Estoque</h5>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link " href="vendas.php">
                            <h5 class="text-light">Vendas Produtos</h5>

                        </a>
                    </li>

                </ul>
            </div>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center custom-card-header bg-warning">
                    <h2 class="text-center my-4 h2-striped">Cadastro Produto
                        <a class="btn btn-primary" href="cadastro.php">+Adicionar</a>
                    </h2>
                    <a class="btn btn-dark" href="movimentacoes.php">Controle de Estoque</a>

                    <div class="text-center mt-3" >
                        
                            <?php
                            // Adiciona um botão/link para sair (redirecionando para a tela de login)
                            echo '<a class="btn btn-danger" style="font-size: 17px; padding: 6px 2rem;" href="login.php">Sair</a>';
                            ?>
                      
                    </div>



                </div>
                <div class="card-body">
                    <!-- Tabela de Cadastro de Produtos -->
                    <table class="table table-striped table-dark">
                        <!-- Cabeçalho da Tabela -->
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Descrição</th>
                                <th>Valor Unitário</th>
                                <th>Unidade de Medida</th>

                                <th></th>
                            </tr>
                        </thead>
                        <!-- Corpo da Tabela -->
                        <tbody>
                            <?php foreach ($results as $item) : ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td><?= $item['produto'] ?></td>
                                    <td><?= $item['quantidade'] ?></td>
                                    <td><?= $arrayDescricao[$item['descricao']] ?></td>
                                    <td><?= $item['valor_unitario'] ?></td>
                                    <td><?= $item['unidade_medida'] ?></td>

                                    <td>
                                        <a class="btn btn-sm btn-primary" href="cadastro.php?id=<?= $item['id'] ?>">Editar</a>
                                        <button class="btn btn-sm btn-danger" onclick="excluir(<?= $item['id'] ?>)">Excluir</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    function excluir(id) {
        if (confirm("Deseja Excluir este cadastro?")) {
            window.location.href = "index.php?excluir=" + id;
        }
    }
</script>

<?php
include_once("./layout/_footer.php");
?>