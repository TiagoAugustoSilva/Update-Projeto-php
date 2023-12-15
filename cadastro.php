<?php
require_once("conexao.php");

$id = 0;
$produto = '';
$descricao = '';
$valor_unitario = '';
$unidade_medida = '';
$quantidade = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = isset($_POST['id']) ? filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT) : null;
    $produto = filter_input(INPUT_POST, "produto", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descricao = filter_input(INPUT_POST, "descricao", FILTER_SANITIZE_NUMBER_INT);
    $valor_unitario = filter_input(INPUT_POST, "valor_unitario", FILTER_SANITIZE_NUMBER_FLOAT);
    $unidade_medida = filter_input(INPUT_POST, "unidade_medida", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $quantidade = filter_input(INPUT_POST, "quantidade", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$id) {
        // Se $id não existe, prepara a instrução INSERT
        $stm = $conn->prepare("INSERT INTO cadastrar_produto (produto, descricao, valor_unitario, unidade_medida, quantidade) VALUES (:produto, :descricao, :valor_unitario, :unidade_medida, :quantidade)");
    } else {
        // Se $id existe, prepara a instrução UPDATE
        $stm = $conn->prepare("UPDATE cadastrar_produto SET produto=:produto, descricao=:descricao, valor_unitario=:valor_unitario, unidade_medida=:unidade_medida, quantidade=:quantidade WHERE id=:id");
        $stm->bindValue(':id', $id);
    }

    // Adicione as linhas de vinculação abaixo para garantir que todos os parâmetros sejam vinculados corretamente
    $stm->bindValue(':produto', $produto);
    $stm->bindValue(':descricao', $descricao);
    $stm->bindValue(':valor_unitario', $valor_unitario);
    $stm->bindValue(':unidade_medida', $unidade_medida);
    $stm->bindValue(':quantidade', $quantidade);


    $stm->execute();

    header('Location: index.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);

    if (!$id) {
        header('Location: index.php');
        exit;
    }

    $stm = $conn->prepare('SELECT * FROM cadastrar_produto WHERE id=:id');
    $stm->bindValue(':id', $id);
    $stm->execute();
    $result = $stm->fetch();

    if (!$result) {
        header('Location: index.php');
        exit;
    }

    $id = $result['id'];
    $produto = $result['produto'];
    $descricao = $result['descricao'];
    $valor_unitario = $result['valor_unitario'];
    $unidade_medida = $result['unidade_medida'];
    $quantidade = $result['quantidade'];
}

include_once("./layout/_header.php");
?>
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-warning">
        <h3 class="text-center mt-3"><?= $id ? ' Editar Produto ' : 'Adicionar Produto ' ?></h3>
        <?php if ($id) : ?>
            <a class="btn btn-warning" href="cadastro.php?id=<?= $id ?>"></a>
        <?php endif; ?>
    </div>

    <form method="post" autocomplete="off">
        <div class="card-body">
            <input type="hidden" name="id" value="<?= $id ?>" />

            <div class="form-group">
                <label for="produto">Produto</label>
                <input type="text" class="form-control" id="produto" name="produto" value="<?= $produto ?>" required />
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <select class="form-select" id="descricao" name="descricao" required>
                    <option value="1" <?= $descricao == 1 ? 'selected' : '' ?>>Eletrônicos</option>
                    <option value="2" <?= $descricao == 2 ? 'selected' : '' ?>>Informática</option>
                    <option value="3" <?= $descricao == 3 ? 'selected' : '' ?>>Domésticos</option>
                    <option value="4" <?= $descricao == 4 ? 'selected' : '' ?>>Celulares</option>
                    <option value="5" <?= $descricao == 5 ? 'selected' : '' ?>>Acessórios</option>
                </select>
            </div>
            <div class="form-group">
                <label for="valor_unitario">Valor Unitário</label>
                <input type="text" class="form-control" id="valor_unitario" name="valor_unitario" value="<?= $valor_unitario ?>" required />
            </div>
            <div class="form-group">
                <label for="unidade_medida">Unidade de Medida</label>
                <input type="text" class="form-control" id="unidade_medida" name="unidade_medida" value="<?= $unidade_medida ?>" required />
            </div>
            <div class="form-group">
                <label for="quantidade">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="<?= $Quantidade ?>" required />
            </div>
        </div>
        <!--style height altera a altura que o card irá ter!-->
        <div class="card-footer" style="height: 200px;">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a class="btn btn-danger" href="index.php">Voltar</a>

           
        </div>
    </form>
</div>

<?php
include_once("./layout/_footer.php");
?>