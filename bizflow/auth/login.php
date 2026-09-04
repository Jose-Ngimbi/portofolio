<?php
session_start();

if (isset($_SESSION["id_usuario"])) {

    header("Location: ../dashboard/index.php");
    exit;

}


require_once "../config/database.php";


$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {

        $sql = "SELECT id_usuario, nome, email, senha, nivel, status
                FROM usuarios
                WHERE email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Erro na preparação da consulta.");
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {

            $usuario = $resultado->fetch_assoc();

            if ($usuario["status"] !== "ativo") {

                $erro = "A sua conta está inativa.";

            } elseif (password_verify($senha, $usuario["senha"])) {

                session_regenerate_id(true);

                $_SESSION["id_usuario"] = $usuario["id_usuario"];
                $_SESSION["nome"] = $usuario["nome"];
                $_SESSION["email"] = $usuario["email"];
                $_SESSION["nivel"] = $usuario["nivel"];

                header("Location: ../dashboard/index.php");
                exit;

            } else {

                $erro = "Email ou senha incorretos.";

            }

        } else {

            $erro = "Email ou senha incorretos.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BizFlow</title>
</head>

<body>

    <h1>BizFlow</h1>

    <h2>Entrar</h2>

    <?php if (!empty($erro)): ?>
        <p><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <form method="POST">

        <label for="email">Email</label>
        <br>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <br><br>

        <label for="senha">Senha</label>
        <br>
        <input
            type="password"
            id="senha"
            name="senha"
            required
        >

        <br><br>

        <button type="submit">Entrar</button>

    </form>

</body>
</html>