<?php
require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente",
    "funcionario"
]);


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}


$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id) {
    header("Location: index.php?erro=Produto inválido.");
    exit;
}


$sql = "DELETE FROM produtos
        WHERE id_produto = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: index.php?erro=Erro ao preparar a exclusão.");
    exit;
}


$stmt->bind_param("i", $id);


if ($stmt->execute() && $stmt->affected_rows === 1) {

    header(
        "Location: index.php?sucesso=Produto excluído com sucesso!"
    );

} else {

    header(
        "Location: index.php?erro=Não foi possível excluir o produto."
    );
}


$stmt->close();
$conn->close();

exit;
