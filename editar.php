<?php
require_once('conexao.php');

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    $stmt = $conn->prepare('SELECT * FROM movimentacoes WHERE id = :id');
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $movimentacao = $stmt->fetch(PDO::FETCH_ASSOC);

    // Inclua o cabeçalho aqui
    include_once("./layout/_header.php");
} else {
    // Redirecione para a página principal se nenhum ID for fornecido
    header('Location: index.php');
    exit;
}
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

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-warning">
                <h1 class="text-center mb-4">Controle de Estoque</h1>
            </div>

            <div class="card-body">

                <!-- Botão para abrir o modal -->

                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editarModal">
                    Editar Movimentação
                </button>

                <!-- Modal de Edição -->
                <div class="modal" tabindex="-1" role="dialog" id="editarModal">
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
                                            <option value="1" <?= $movimentacao['tipo_documento'] == 1 ? 'selected' : '' ?>>CNPJ</option>
                                            <option value="2" <?= $movimentacao['tipo_documento'] == 2 ? 'selected' : '' ?>>CPF</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="numero_documento">Número do Documento:</label>
                                        <input type="text" class="form-control" name="numero_documento" value="<?= $movimentacao['numero_documento'] ?>" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Salvar Edição</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>