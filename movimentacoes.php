<?php

require_once('conexao.php');

$data_movimentacao = '';
$quantidade = '';
$tipo_documento = '';
$numero_documento = '';




// atenção: este get está associado ao botão excluir, junto ao get global do código.
if (isset($_GET['excluir'])) {
    $id = filter_input(INPUT_GET, 'excluir', FILTER_SANITIZE_NUMBER_INT);

    if ($id) {
        $conn->exec('DELETE FROM movimentacoes WHERE id=' . $id);

        header('Location: movimentacoes.php');
        exit;
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data_movimentacao = $_POST['data_movimentacao'];
    $quantidade = $_POST['quantidade'];
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = $_POST['numero_documento'];

    $stmt = $conn->prepare("INSERT INTO movimentacoes (data_movimentacao, quantidade, tipo_documento, numero_documento) VALUES (:data_movimentacao, :quantidade, :tipo_documento, :numero_documento)");
    $stmt->bindParam(':data_movimentacao', $data_movimentacao);
    $stmt->bindParam(':quantidade', $quantidade);
    $stmt->bindParam(':tipo_documento', $tipo_documento);
    $stmt->bindParam(':numero_documento', $numero_documento);
    $stmt->execute();
}


// área de conexão com a tabela no banco de dados
$stm = $conn->query('SELECT * FROM movimentacoes');
$movimentacoes = $stm->fetchAll();

$results = $conn->query("select * from movimentacoes")->fetchAll();

$arrayMovimentacao = [1 => 'CNPJ', 2 => 'CPF'];



include_once("./layout/_header.php");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body class="bg-dark">

    <div class="card mt-4">
        <div class="card-header bg-warning">
            <h1 class="text-center mb-4">Controle de Estoque</h1>
        </div>

        <div class="card-body">
            <h2 class="text-center mb-4">Movimentações</h2>
            <!-- Botão +Adicionar -->
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#adicionarModal">
                +Adicionar
            </button>

            <a class="btn btn-danger" href="index.php">Voltar</a>

            <table class="table table-bordered mt-5 ">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Data Movimentação</th>
                        <th>Quantidade</th>
                        <th>Tipo de Documento</th>
                        <th>Número do Documento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimentacoes as $movimentacao) : ?>
                        <tr>
                            <td><?= $movimentacao['id'] ?></td>
                            <td><?= $movimentacao['data_movimentacao'] ?></td>
                            <td><?= $movimentacao['quantidade'] ?></td>
                            <td><?= ($movimentacao['tipo_documento'] == 1) ? 'CNPJ' : 'CPF' ?></td>
                            <td><?= $movimentacao['numero_documento'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editarModal<?= $movimentacao['id'] ?>">Editar</button>
                                <button class="btn btn-sm btn-danger" onclick="excluir(<?= $movimentacao['id'] ?>)">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <!-- Conteúdo do rodapé do card -->
        </div>

        <?php foreach ($movimentacoes as $movimentacao) : ?>
            <!-- Modal de Edição -->
            <div class="modal fade" id="editarModal<?= $movimentacao['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <!-- Conteúdo do modal -->
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Movimentação</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Formulário de Edição -->
                            <form method="post" action="salvar_edicao.php">
                                <input type="hidden" name="id" value="<?= $movimentacao['id'] ?>">
                                <div class="form-group">
                                    <label for="data_movimentacao">Data Movimentação:</label>
                                    <input type="date" class="form-control" name="data_movimentacao" value="<?= $movimentacao['data_movimentacao'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="quantidade">Quantidade:</label>
                                    <input type="number" class="form-control" name="quantidade" value="<?= $movimentacao['quantidade'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="tipo_documento">Tipo de Documento:</label>
                                    <select class="form-select form-control-sm mt-2" id="tipo_documento" name="tipo_documento" required>
                                        <option value="1" <?= ($movimentacao['tipo_documento'] == 'CNPJ') ? 'selected' : '' ?>>CNPJ</option>
                                        <option value="2" <?= ($movimentacao['tipo_documento'] == 'CPF') ? 'selected' : '' ?>>CPF</option>
                                    </select>


                                </div>
                                <div class="form-group">
                                    <label for="numero_documento">Número do Documento:</label>
                                    <input type="text" class="form-control" name="numero_documento" value="<?= $movimentacao['numero_documento'] ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Salvar Edição</button>
                            </form>
                        </div>
                    </div>F
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Modal de Adição -->
        <div class="modal fade" id="adicionarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <!-- Conteúdo do modal -->
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Adicionar Movimentação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulário de Adição -->
                        <form id="adicionarForm" method="post" action="movimentacoes.php">
                            <div class="form-group">
                                <label for="data_movimentacao">Data Movimentação:</label>
                                <input type="date" class="form-control" name="data_movimentacao" required>
                            </div>
                            <div class="form-group">
                                <label for="quantidade">Quantidade:</label>
                                <input type="number" class="form-control" name="quantidade" required>
                            </div>
                            <div class="form-group">
                                <label for="tipo_documento">Tipo de Documento:</label>
                                <select class="form-select form-control-sm mt-2" id="tipo_documento" name="tipo_documento" required>
                                    <option value="1">CNPJ</option>
                                    <option value="2">CPF</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="numero_documento">Número do Documento:</label>
                                <input type="text" class="form-control" name="numero_documento" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <!--No seu código, a função submitForm() é responsável por enviar os dados do formulário para o servidor usando AJAX.-->
                        <button type="button" class="btn btn-primary" onclick="submitForm()">Salvar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function submitForm() {
                //está parte está trabalhando com o AJAX para abrir o modal Pop-up
                document.querySelector('#adicionarForm').submit();
                $('#adicionarModal').modal('hide'); // Fecha o modal após o envio do formulário
            }
        </script>
    </div>
    </div>

    <script>
        function excluir(id) {
            if (confirm("Deseja Excluir esta movimentação?")) {
                window.location.href = "movimentacoes.php?excluir=" + id;
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>