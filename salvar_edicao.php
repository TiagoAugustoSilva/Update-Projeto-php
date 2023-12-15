<?php
require_once('conexao.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $data_movimentacao = $_POST['data_movimentacao'];
    $quantidade = $_POST['quantidade'];
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = $_POST['numero_documento'];

    $stmt = $conn->prepare("UPDATE movimentacoes SET data_movimentacao = :data_movimentacao, quantidade = :quantidade, tipo_documento = :tipo_documento, numero_documento = :numero_documento WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':data_movimentacao', $data_movimentacao);
    $stmt->bindParam(':quantidade', $quantidade);
    $stmt->bindParam(':tipo_documento', $tipo_documento);
    $stmt->bindParam(':numero_documento', $numero_documento);
    $stmt->execute();

    // Redirecione de volta para a página principal após a edição
    header('Location: movimentacoes.php');
    exit;
} else {
    // Redirecione para a página principal se não for uma solicitação POST
    header('Location: index.php');
    exit;
}
?>