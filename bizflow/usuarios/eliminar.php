<?php
require_once "../includes/admin_only.php";
require_once "../config/database.php";


// ==========================================
// VERIFICAR SESSÃO
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../auth/login.php");
    exit;

}


// ==========================================
// VERIFICAR ID
// ==========================================

if (!isset($_GET["id"])) {

    header("Location: index.php");
    exit;

}

$id = (int) $_GET["id"];


// ==========================================
// IMPEDIR ELIMINAR A PRÓPRIA CONTA
// ==========================================

if ($id === (int) $_SESSION["id_usuario"]) {

    header(
        "Location: index.php?erro=propria_conta"
    );

    exit;

}


// ==========================================
// VERIFICAR SE O USUÁRIO EXISTE
// ==========================================

$sql = "
    SELECT
        id_usuario,
        nivel
    FROM usuarios
    WHERE id_usuario = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    header(
        "Location: index.php?erro=nao_encontrado"
    );

    exit;

}

$usuario = $resultado->fetch_assoc();

$stmt->close();


// ==========================================
// IMPEDIR ELIMINAR O ÚLTIMO ADMINISTRADOR
// ==========================================

if ($usuario["nivel"] === "administrador") {

    $sql_admin = "
        SELECT COUNT(*) AS total
        FROM usuarios
        WHERE nivel = 'administrador'
    ";

    $resultado_admin =
        $conn->query($sql_admin);

    $dados_admin =
        $resultado_admin->fetch_assoc();

    $total_admin =
        (int) $dados_admin["total"];


    if ($total_admin <= 1) {

        header(
            "Location: index.php?erro=ultimo_admin"
        );

        exit;

    }

}


// ==========================================
// ELIMINAR USUÁRIO
// ==========================================

$sql_eliminar = "
    DELETE FROM usuarios
    WHERE id_usuario = ?
";

$stmt = $conn->prepare(
    $sql_eliminar
);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$stmt->close();


// ==========================================
// VOLTAR PARA A LISTAGEM
// ==========================================

header(
    "Location: index.php?sucesso=eliminado"
);

exit;