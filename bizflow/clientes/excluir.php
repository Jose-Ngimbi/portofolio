<?php

session_start();
require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente"
]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}


$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php?erro=Cliente inválido.");
    exit;
}


$sql = "DELETE FROM clientes
        WHERE id_cliente = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: index.php?erro=Erro ao preparar a exclusão.");
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: index.php?sucesso=Cliente excluído com sucesso!");

} else {

    header("Location: index.php?erro=Não foi possível excluir o cliente.");
}

$stmt->close();
$conn->close();

exit;