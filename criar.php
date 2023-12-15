<?php
// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conecta ao banco de dados
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "cadastrar_produto";
    $port = 3306;

    try {
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtém os dados do formulário
        $newUsername = $_POST["new_username"];
        //utilizamos o hash para armazenar a senha de forma segura
        $newPassword = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

        // Proteção contra SQL injection usando prepared statements
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $newUsername);
        $stmt->bindParam(':password', $newPassword);
        $stmt->execute();

        // Redireciona para a página de login após o cadastro bem-sucedido
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    } finally {
        // Fecha a conexão
        $conn = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Cadastro</title>
</head>

<body class="d-flex align-items-center justify-content-center bg-dark" style="height: 100vh;">

    <div class="card text-center bg-light" style="width: 35%; height: 65%;">
        <div class="card-header bg-warning">
            <h2>Cadastro</h2>
        </div>
        <div class="card-body d-flex justify-content-center">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="text-start">
                <div class="mb-3 text-center">
                    <label for="new_username" class="form-label align-items-center">Novo Nome de Usuário:</label>
                    <input type="text" name="new_username" class="form-control" id="new_username" required>
                </div>
                <div class="mb-3 text-center">
                    <label for="new_password" class="form-label">Nova Senha:</label>
                    <input type="password" name="new_password" class="form-control" id="new_password" required>
                </div>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                    <a href="login.php" class="btn btn-danger">voltar</a>
                 
            
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
