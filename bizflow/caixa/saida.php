<?php


require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente"
]);

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $valor = $_POST["valor"] ?? "";
    $descricao = trim($_POST["descricao"] ?? "");

    if (!is_numeric($valor) || $valor <= 0) {

        $erro = "Informe um valor válido.";

    } elseif ($descricao === "") {

        $erro = "Informe a descrição da saída.";

    } else {

        $valor = (float) $valor;

        // Verificar saldo disponível
        $sql_saldo = "
            SELECT
                COALESCE(
                    SUM(
                        CASE
                            WHEN tipo = 'entrada' THEN valor
                            WHEN tipo = 'saida' THEN -valor
                        END
                    ),
                    0
                ) AS saldo
            FROM movimentos_caixa
        ";

        $resultado_saldo = $conn->query($sql_saldo);

        $saldo = (float) $resultado_saldo->fetch_assoc()["saldo"];


        if ($valor > $saldo) {

            $erro =
                "Saldo insuficiente. Saldo disponível: "
                . number_format(
                    $saldo,
                    2,
                    ",",
                    "."
                )
                . " Kz";

        } else {

            $tipo = "saida";

            $id_usuario = $_SESSION["id_usuario"];

            $sql = "
                INSERT INTO movimentos_caixa
                (
                    id_usuario,
                    tipo,
                    valor,
                    descricao
                )
                VALUES (?, ?, ?, ?)
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "isds",
                $id_usuario,
                $tipo,
                $valor,
                $descricao
            );

            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: index.php?sucesso=Saída registrada com sucesso!"
                );

                exit;

            } else {

                $erro =
                    "Não foi possível registrar a saída.";

                $stmt->close();
            }
        }
    }
}
require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>

<main class="main">

    <header class="topbar">

        <h1>Nova Saída</h1>

        <div class="user-info">

            <div>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $_SESSION["nome"]
                    );
                    ?>
                </strong>

                <small class="d-block text-muted">

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["nivel"]
                    );
                    ?>

                </small>

            </div>

            <div class="user-avatar">

                <?php

                echo strtoupper(
                    substr(
                        $_SESSION["nome"],
                        0,
                        1
                    )
                );

                ?>

            </div>

        </div>

    </header>


    <section class="content">

        <div class="mb-4">

            <h2 class="fw-semibold">
                Registrar Saída
            </h2>

            <p class="text-muted">
                Registre uma despesa ou retirada do caixa.
            </p>

        </div>


        <?php if ($erro !== ""): ?>

            <div class="alert alert-danger">

                <?php
                echo htmlspecialchars($erro);
                ?>

            </div>

        <?php endif; ?>


        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <form method="POST">

                    <div class="mb-4">

                        <label
                            for="valor"
                            class="form-label"
                        >
                            Valor *
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                name="valor"
                                id="valor"
                                class="form-control"
                                min="0.01"
                                step="0.01"
                                placeholder="0,00"
                                required
                            >

                            <span class="input-group-text">
                                Kz
                            </span>

                        </div>

                    </div>


                    <div class="mb-4">

                        <label
                            for="descricao"
                            class="form-label"
                        >
                            Descrição *
                        </label>

                        <input
                            type="text"
                            name="descricao"
                            id="descricao"
                            class="form-control"
                            maxlength="255"
                            placeholder="Ex.: Compra de material de escritório"
                            required
                        >

                    </div>


                    <div class="d-flex gap-2">

                        <a
                            href="index.php"
                            class="btn btn-light"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-danger"
                        >
                            Registrar saída
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>