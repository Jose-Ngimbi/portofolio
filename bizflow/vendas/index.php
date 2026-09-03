<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente",
    "funcionario"
]);

$sucesso = $_GET["sucesso"] ?? "";
$erro = $_GET["erro"] ?? "";


// Buscar vendas
$sql = "SELECT
            v.id_venda,
            v.data_venda,
            v.subtotal,
            v.desconto,
            v.total,
            v.forma_pagamento,
            c.nome AS cliente,
            u.nome AS usuario
        FROM vendas v

        LEFT JOIN clientes c
            ON v.id_cliente = c.id_cliente

        INNER JOIN usuarios u
            ON v.id_usuario = u.id_usuario

        ORDER BY v.id_venda DESC";

$resultado = $conn->query($sql);

?>

<main class="main">

    <header class="topbar">

        <h1>Vendas</h1>

        <div class="user-info">

            <div>

                <strong>
                    <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                </strong>

                <small class="d-block text-muted">
                    <?php echo htmlspecialchars($_SESSION["nivel"]); ?>
                </small>

            </div>

            <div class="user-avatar">

                <?php
                echo strtoupper(
                    substr($_SESSION["nome"], 0, 1)
                );
                ?>

            </div>

        </div>

    </header>


    <section class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-semibold">
                    Vendas
                </h2>

                <p class="text-muted mb-0">
                    Consulte e gerencie as vendas realizadas.
                </p>

            </div>


            <a
                href="nova.php"
                class="btn btn-success"
            >
                + Nova venda
            </a>

        </div>


        <?php if ($sucesso !== ""): ?>

            <div class="alert alert-success">
                <?php echo htmlspecialchars($sucesso); ?>
            </div>

        <?php endif; ?>


        <?php if ($erro !== ""): ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($erro); ?>
            </div>

        <?php endif; ?>


        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Subtotal</th>
                                <th>Desconto</th>
                                <th>Total</th>
                                <th>Pagamento</th>
                                <th>Usuário</th>
                                <th>Ações</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($venda = $resultado->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        #<?php echo $venda["id_venda"]; ?>
                                    </td>


                                    <td>

                                        <?php
                                        echo date(
                                            "d/m/Y H:i",
                                            strtotime(
                                                $venda["data_venda"]
                                            )
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo $venda["cliente"]
                                            ? htmlspecialchars(
                                                $venda["cliente"]
                                            )
                                            : "Cliente não identificado";
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo number_format(
                                            $venda["subtotal"],
                                            2,
                                            ",",
                                            "."
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo number_format(
                                            $venda["desconto"],
                                            2,
                                            ",",
                                            "."
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?php
                                            echo number_format(
                                                $venda["total"],
                                                2,
                                                ",",
                                                "."
                                            );
                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        $pagamentos = [

                                            "dinheiro" =>
                                                "Dinheiro",

                                            "transferencia" =>
                                                "Transferência",

                                            "multicaixa" =>
                                                "Multicaixa",

                                            "cartao" =>
                                                "Cartão"

                                        ];

                                        echo htmlspecialchars(
                                            $pagamentos[
                                                $venda["forma_pagamento"]
                                            ]
                                            ?? $venda["forma_pagamento"]
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $venda["usuario"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <a
                                            href="visualizar.php?id=<?php echo $venda["id_venda"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Ver
                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center py-5"
                                >

                                    <p class="text-muted mb-0">
                                        Nenhuma venda registrada.
                                    </p>

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