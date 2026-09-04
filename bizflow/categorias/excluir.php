<?php
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


$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: index.php?erro=Categoria inválida.");
    exit;
}


// Excluir categoria
$sql = "DELETE FROM categorias
        WHERE id_categoria = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: index.php?erro=Erro ao preparar a exclusão.");
    exit;
}

$stmt->bind_param("i", $id);


if ($stmt->execute()) {

    header(
        "Location: index.php?sucesso=Categoria excluída com sucesso!"
    );

} else {

    header(
        "Location: index.php?erro=Não foi possível excluir a categoria."
    );
}


$stmt->close();
$conn->close();

exit;