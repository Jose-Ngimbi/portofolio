<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "portofolio";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na ligação com a base de dados: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>