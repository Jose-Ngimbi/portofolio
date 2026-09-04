<?php

// ==========================================
// VERIFICAR SESSÃO
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// VERIFICAR LOGIN
// ==========================================

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../auth/login.php");
    exit;

}

// ==========================================
// FUNÇÃO PARA VERIFICAR NÍVEL
// ==========================================

function permitirAcesso($niveisPermitidos)
{

    if (!in_array($_SESSION["nivel"], $niveisPermitidos, true)) {

        header("Location: ../dashboard/");
        exit;

    }

}