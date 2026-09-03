<?php

// ==========================================
// INICIAR SESSÃO
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// VERIFICAR SE ESTÁ LOGADO
// ==========================================

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../auth/login.php");
    exit;

}

// ==========================================
// VERIFICAR SE É ADMINISTRADOR
// ==========================================

if ($_SESSION["nivel"] !== "administrador") {

    header("Location: ../dashboard/");
    exit;

}