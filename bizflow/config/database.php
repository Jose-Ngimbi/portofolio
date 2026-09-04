<?php

// Fazer o MySQLi lançar Exceptions em caso de erro
mysqli_report(
    MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT
);

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "portofolio";

try {

    $conn = new mysqli(
        $host,
        $usuario,
        $senha,
        $banco
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    die(
        "Erro na ligação com a base de dados."
    );
}
?>