<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente"
]);


// ==========================================
// TOTAL DE ENTRADAS
// ==========================================

$sql_entradas = "
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM movimentos_caixa
    WHERE tipo = 'entrada'
";

$resultado_entradas = $conn->query($sql_entradas);

$entradas = $resultado_entradas->fetch_assoc()["total"];


// ==========================================
// TOTAL DE SAÍDAS
// ==========================================

$sql_saidas = "
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM movimentos_caixa
    WHERE tipo = 'saida'
";

$resultado_saidas = $conn->query($sql_saidas);

$saidas = $resultado_saidas->fetch_assoc()["total"];


// ==========================================
// SALDO
// ==========================================

$saldo = $entradas - $saidas;


// ==========================================
// HISTÓRICO
// ==========================================

$sql_movimentos = "
    SELECT
        m.id_movimento,
        m.tipo,
        m.valor,
        m.descricao,
        m.data_movimento,
        u.nome AS usuario

    FROM movimentos_caixa m

    INNER JOIN usuarios u
        ON m.id_usuario = u.id_usuario

    ORDER BY m.id_movimento DESC
";

$movimentos = $conn->query($sql_movimentos);

?>

<main class="main">

    <header class="topbar">

        <h1>Caixa</h1>

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


        <!-- CABEÇALHO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-semibold mb-1">
                    Caixa
                </h2>

                <p class="text-muted mb-0">
                    Controle as entradas, saídas e saldo da empresa.
                </p>

            </div>


            <div class="d-flex gap-2">

                <a
                    href="entrada.php"
                    class="btn btn-success"
                >
                    + Entrada
                </a>

                <a
                    href="saida.php"
                    class="btn btn-outline-danger"
                >
                    − Saída
                </a>

            </div>

        </div>


        <!-- CARDS -->

        <div class="row g-4 mb-4">


            <!-- ENTRADAS -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted mb-2">
                            Total de entradas
                        </div>

                        <h3 class="text-success mb-0">

                            <?php

                            echo number_format(
                                $entradas,
                                2,
                                ",",
                                "."
                            );

                            ?>

                            Kz

                        </h3>

                    </div>

                </div>

            </div>


            <!-- SAÍDAS -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted mb-2">
                            Total de saídas
                        </div>

                        <h3 class="text-danger mb-0">

                            <?php

                            echo number_format(
                                $saidas,
                                2,
                                ",",
                                "."
                            );

                            ?>

                            Kz

                        </h3>

                    </div>

                </div>

            </div>


            <!-- SALDO -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted mb-2">
                            Saldo atual
                        </div>

                        <h3 class="mb-0">

                            <?php

                            echo number_format(
                                $saldo,
                                2,
                                ",",
                                "."
                            );

                            ?>

                            Kz

                        </h3>

                    </div>

                </div>

            </div>

        </div>


        <!-- HISTÓRICO -->

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <h5 class="mb-4">
                    Histórico do caixa
                </h5>


                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Data</th>

                                <th>Tipo</th>

                                <th>Descrição</th>

                                <th>Valor</th>

                                <th>Usuário</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($movimentos && $movimentos->num_rows > 0): ?>

                            <?php while ($movimento = $movimentos->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        #<?php
                                        echo $movimento["id_movimento"];
                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo date(
                                            "d/m/Y H:i",
                                            strtotime(
                                                $movimento["data_movimento"]
                                            )
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php if ($movimento["tipo"] === "entrada"): ?>

                                            <span class="badge bg-success">
                                                Entrada
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">
                                                Saída
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $movimento["descricao"]
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <strong
                                            class="<?php echo $movimento["tipo"] === "entrada"
                                                ? "text-success"
                                                : "text-danger";
                                            ?>"
                                        >

                                            <?php

                                            echo $movimento["tipo"] === "entrada"
                                                ? "+"
                                                : "-";

                                            ?>

                                            <?php

                                            echo number_format(
                                                $movimento["valor"],
                                                2,
                                                ",",
                                                "."
                                            );

                                            ?>

                                            Kz

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $movimento["usuario"]
                                        );

                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center text-muted py-5"
                                >

                                    Nenhum movimento registrado.

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>