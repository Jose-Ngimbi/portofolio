<?php


require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente",
    "funcionario"
]);




// ==========================================
// VALIDAR ID DA VENDA
// ==========================================

$id_venda = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id_venda) {
    header("Location: index.php");
    exit;
}


// ==========================================
// BUSCAR DADOS DA VENDA
// ==========================================

$sql = "SELECT
            v.id_venda,
            v.data_venda,
            v.subtotal,
            v.desconto,
            v.total,
            v.forma_pagamento,
            c.nome AS cliente,
            c.telefone,
            c.email,
            u.nome AS usuario

        FROM vendas v

        LEFT JOIN clientes c
            ON v.id_cliente = c.id_cliente

        INNER JOIN usuarios u
            ON v.id_usuario = u.id_usuario

        WHERE v.id_venda = ?

        LIMIT 1";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id_venda
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php?erro=Venda não encontrada.");
    exit;
}


$venda = $resultado->fetch_assoc();

$stmt->close();


// ==========================================
// BUSCAR ITENS DA VENDA
// ==========================================

$sql_itens = "SELECT
                vi.id_item,
                vi.quantidade,
                vi.preco_unitario,
                vi.subtotal,
                p.nome AS produto

              FROM venda_itens vi

              INNER JOIN produtos p
                ON vi.id_produto = p.id_produto

              WHERE vi.id_venda = ?

              ORDER BY vi.id_item ASC";


$stmt = $conn->prepare($sql_itens);

$stmt->bind_param(
    "i",
    $id_venda
);

$stmt->execute();

$itens = $stmt->get_result();

$stmt->close();


// ==========================================
// FORMA DE PAGAMENTO
// ==========================================

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

$forma_pagamento =
    $pagamentos[
        $venda["forma_pagamento"]
    ]
    ?? $venda["forma_pagamento"];

?>

<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";

?>

<main class="main">

    <header class="topbar">

        <h1>Detalhes da Venda</h1>

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

                    Venda
                    #<?php
                    echo $venda["id_venda"];
                    ?>

                </h2>

                <p class="text-muted mb-0">

                    Detalhes da venda realizada.

                </p>

            </div>


            <div class="d-flex gap-2">

                <a
                    href="index.php"
                    class="btn btn-light"
                >
                    ← Voltar
                </a>


                <button
                    type="button"
                    onclick="window.print()"
                    class="btn btn-outline-primary"
                >
                    🖨️ Imprimir
                </button>

            </div>

        </div>


        <!-- INFORMAÇÕES -->

        <div class="row g-4 mb-4">


            <!-- CLIENTE -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <h6 class="text-muted mb-3">
                            Cliente
                        </h6>


                        <?php if ($venda["cliente"]): ?>

                            <h5 class="mb-2">

                                <?php
                                echo htmlspecialchars(
                                    $venda["cliente"]
                                );
                                ?>

                            </h5>


                            <?php if ($venda["telefone"]): ?>

                                <div class="text-muted">

                                    📞
                                    <?php
                                    echo htmlspecialchars(
                                        $venda["telefone"]
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                            <?php if ($venda["email"]): ?>

                                <div class="text-muted">

                                    ✉️
                                    <?php
                                    echo htmlspecialchars(
                                        $venda["email"]
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                        <?php else: ?>

                            <p class="text-muted mb-0">
                                Venda sem cliente.
                            </p>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- VENDA -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <h6 class="text-muted mb-3">
                            Informações da venda
                        </h6>


                        <div class="mb-2">

                            <span class="text-muted">
                                Número:
                            </span>

                            <strong>
                                #<?php
                                echo $venda["id_venda"];
                                ?>
                            </strong>

                        </div>


                        <div class="mb-2">

                            <span class="text-muted">
                                Data:
                            </span>

                            <?php

                            echo date(
                                "d/m/Y H:i",
                                strtotime(
                                    $venda["data_venda"]
                                )
                            );

                            ?>

                        </div>


                        <div>

                            <span class="text-muted">
                                Vendedor:
                            </span>

                            <?php
                            echo htmlspecialchars(
                                $venda["usuario"]
                            );
                            ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- PAGAMENTO -->

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <h6 class="text-muted mb-3">
                            Pagamento
                        </h6>


                        <h5>

                            <?php
                            echo htmlspecialchars(
                                $forma_pagamento
                            );
                            ?>

                        </h5>


                        <p class="text-muted mb-0">

                            Venda paga através de
                            <?php
                            echo strtolower(
                                htmlspecialchars(
                                    $forma_pagamento
                                )
                            );
                            ?>.

                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- PRODUTOS -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <h5 class="mb-4">
                    Produtos da venda
                </h5>


                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>
                                    Produto
                                </th>

                                <th>
                                    Quantidade
                                </th>

                                <th>
                                    Preço unitário
                                </th>

                                <th>
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($itens->num_rows > 0): ?>

                            <?php while ($item = $itens->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $item["produto"]
                                            );
                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?php
                                        echo $item["quantidade"];
                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo number_format(
                                            $item["preco_unitario"],
                                            2,
                                            ",",
                                            "."
                                        );

                                        ?>

                                        Kz

                                    </td>


                                    <td>

                                        <strong>

                                            <?php

                                            echo number_format(
                                                $item["subtotal"],
                                                2,
                                                ",",
                                                "."
                                            );

                                            ?>

                                            Kz

                                        </strong>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center text-muted py-4"
                                >

                                    Nenhum item encontrado.

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- RESUMO -->

        <div class="row justify-content-end">

            <div class="col-md-5">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">


                        <div class="d-flex justify-content-between mb-3">

                            <span>
                                Subtotal
                            </span>

                            <strong>

                                <?php

                                echo number_format(
                                    $venda["subtotal"],
                                    2,
                                    ",",
                                    "."
                                );

                                ?>

                                Kz

                            </strong>

                        </div>


                        <div class="d-flex justify-content-between mb-3">

                            <span>
                                Desconto
                            </span>

                            <strong>

                                <?php

                                echo number_format(
                                    $venda["desconto"],
                                    2,
                                    ",",
                                    "."
                                );

                                ?>

                                Kz

                            </strong>

                        </div>


                        <hr>


                        <div class="d-flex justify-content-between">

                            <strong class="fs-5">
                                TOTAL
                            </strong>

                            <strong class="fs-5">

                                <?php

                                echo number_format(
                                    $venda["total"],
                                    2,
                                    ",",
                                    "."
                                );

                                ?>

                                Kz

                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>


<style>

@media print {

    .sidebar,
    .topbar,
    .btn,
    button {
        display: none !important;
    }

    .main {
        margin: 0 !important;
        padding: 0 !important;
    }

    .content {
        padding: 20px !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }

}

</style>


<?php

require_once "../includes/footer.php";

?>