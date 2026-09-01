<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";


// ==============================
// ESTATÍSTICAS DO DASHBOARD
// ==============================

// Total de clientes
$sql_clientes = "SELECT COUNT(*) AS total FROM clientes";
$resultado_clientes = $conn->query($sql_clientes);

$total_clientes = 0;

if ($resultado_clientes) {
    $dados = $resultado_clientes->fetch_assoc();
    $total_clientes = $dados["total"];
}


// Total de produtos ativos
$sql_produtos = "SELECT COUNT(*) AS total
                 FROM produtos
                 WHERE status = 'ativo'";

$resultado_produtos = $conn->query($sql_produtos);

$total_produtos = 0;

if ($resultado_produtos) {
    $dados = $resultado_produtos->fetch_assoc();
    $total_produtos = $dados["total"];
}


// Produtos com stock baixo
$sql_stock = "SELECT COUNT(*) AS total
              FROM produtos
              WHERE status = 'ativo'
              AND quantidade <= estoque_minimo";

$resultado_stock = $conn->query($sql_stock);

$stock_baixo = 0;

if ($resultado_stock) {
    $dados = $resultado_stock->fetch_assoc();
    $stock_baixo = $dados["total"];
}


// Vendas de hoje
$sql_vendas = "SELECT COALESCE(SUM(total), 0) AS total
               FROM vendas
               WHERE DATE(data_venda) = CURDATE()";

$resultado_vendas = $conn->query($sql_vendas);

$vendas_hoje = 0;

if ($resultado_vendas) {
    $dados = $resultado_vendas->fetch_assoc();
    $vendas_hoje = $dados["total"];
}
// ==============================
// VENDAS DO MÊS
// ==============================

$sql_vendas_mes = "
    SELECT COALESCE(SUM(total), 0) AS total
    FROM vendas
    WHERE MONTH(data_venda) = MONTH(CURDATE())
      AND YEAR(data_venda) = YEAR(CURDATE())
";

$resultado_vendas_mes = $conn->query($sql_vendas_mes);

$vendas_mes = 0;

if ($resultado_vendas_mes) {

    $dados = $resultado_vendas_mes->fetch_assoc();

    $vendas_mes = $dados["total"];
}


// ==============================
// SALDO ATUAL DO CAIXA
// ==============================

$sql_saldo = "
    SELECT COALESCE(
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

$saldo_caixa = 0;

if ($resultado_saldo) {

    $dados = $resultado_saldo->fetch_assoc();

    $saldo_caixa = $dados["saldo"];
}


// ==============================
// ÚLTIMAS VENDAS
// ==============================

$sql_ultimas_vendas = "
    SELECT
        v.id_venda,
        v.data_venda,
        v.total,
        c.nome AS cliente

    FROM vendas v

    LEFT JOIN clientes c
        ON v.id_cliente = c.id_cliente

    ORDER BY v.id_venda DESC
    LIMIT 5
";

$ultimas_vendas = $conn->query($sql_ultimas_vendas);


// ==============================
// PRODUTOS COM STOCK BAIXO
// ==============================

$sql_produtos_baixos = "
    SELECT
        nome,
        quantidade,
        estoque_minimo

    FROM produtos

    WHERE status = 'ativo'
      AND quantidade <= estoque_minimo

    ORDER BY quantidade ASC
";

$produtos_baixos = $conn->query($sql_produtos_baixos);

?>

<main class="main">

    <header class="topbar">

        <h1>Dashboard</h1>

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
                <?php echo strtoupper(substr($_SESSION["nome"], 0, 1)); ?>
            </div>

        </div>

    </header>


    <section class="content">

        <div class="mb-4">

            <h2 class="fw-semibold">
                Visão geral
            </h2>

            <p class="text-muted">
                Aqui está o resumo do seu negócio.
            </p>

        </div>


        <div class="row g-4">


            <!-- VENDAS HOJE -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Vendas hoje
                            </div>

                            <div class="stat-value">

                                <?php
                                echo number_format(
                                    $vendas_hoje,
                                    2,
                                    ",",
                                    "."
                                );
                                ?>

                                Kz

                            </div>

                        </div>

                        <div class="stat-icon">
                            🛒
                        </div>

                    </div>

                </div>

            </div>


            <!-- CLIENTES -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Clientes
                            </div>

                            <div class="stat-value">

                                <?php echo $total_clientes; ?>

                            </div>

                        </div>

                        <div class="stat-icon">
                            👥
                        </div>

                    </div>

                </div>

            </div>


            <!-- PRODUTOS -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Produtos
                            </div>

                            <div class="stat-value">

                                <?php echo $total_produtos; ?>

                            </div>

                        </div>

                        <div class="stat-icon">
                            📦
                        </div>

                    </div>

                </div>

            </div>


            <!-- STOCK BAIXO -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Stock baixo
                            </div>

                            <div class="stat-value">

                                <?php echo $stock_baixo; ?>

                            </div>

                        </div>

                        <div class="stat-icon">
                            ⚠️
                        </div>

                    </div>

                </div>

            </div>


        </div>
        <div class="row g-4 mt-1">

    <!-- VENDAS DO MÊS -->

    <div class="col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="text-muted mb-2">
                    Vendas do mês
                </div>

                <h3 class="mb-0">

                    <?php
                    echo number_format(
                        $vendas_mes,
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


    <!-- SALDO DO CAIXA -->

    <div class="col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="text-muted mb-2">
                    Saldo atual do caixa
                </div>

                <h3 class="mb-0">

                    <?php
                    echo number_format(
                        $saldo_caixa,
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
<!-- ============================= -->
<!-- AÇÕES RÁPIDAS -->
<!-- ============================= -->

<div class="card border-0 shadow-sm mt-4">

    <div class="card-body">

        <h5 class="mb-4">
            Ações rápidas
        </h5>

        <div class="row g-3">

            <!-- NOVA VENDA -->

            <div class="col-md-4 col-lg-2">

                <a
                    href="../vendas/nova.php"
                    class="btn btn-success w-100 py-3"
                >

                    🛒

                    <div class="mt-2">
                        Nova venda
                    </div>

                </a>

            </div>


            <!-- NOVO CLIENTE -->

            <div class="col-md-4 col-lg-2">

                <a
                    href="../clientes/adicionar.php"
                    class="btn btn-outline-primary w-100 py-3"
                >

                    👤

                    <div class="mt-2">
                        Novo cliente
                    </div>

                </a>

            </div>


            <!-- NOVO PRODUTO -->

            <div class="col-md-4 col-lg-2">

                <a
                    href="../produtos/adicionar.php"
                    class="btn btn-outline-dark w-100 py-3"
                >

                    📦

                    <div class="mt-2">
                        Novo produto
                    </div>

                </a>

            </div>


            <!-- ENTRADA -->

            <div class="col-md-4 col-lg-2">

                <a
                    href="../caixa/entrada.php"
                    class="btn btn-outline-success w-100 py-3"
                >

                    💰

                    <div class="mt-2">
                        Entrada
                    </div>

                </a>

            </div>


            <!-- SAÍDA -->

            <div class="col-md-4 col-lg-2">

                <a
                    href="../caixa/saida.php"
                    class="btn btn-outline-danger w-100 py-3"
                >

                    ➖

                    <div class="mt-2">
                        Saída
                    </div>

                </a>

            </div>


            <!-- VER VENDAS -->

            <div class="col-md-4 col-lg-2">

                <a
                    href="../vendas/index.php"
                    class="btn btn-outline-secondary w-100 py-3"
                >

                    📋

                    <div class="mt-2">
                        Vendas
                    </div>

                </a>

            </div>

        </div>

    </div>

</div>
<div class="row g-4 mt-1">

    <!-- ÚLTIMAS VENDAS -->

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h5 class="mb-0">
                        Últimas vendas
                    </h5>

                    <a
                        href="../vendas/index.php"
                        class="btn btn-sm btn-outline-primary"
                    >
                        Ver todas
                    </a>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>Venda</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Total</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if ($ultimas_vendas && $ultimas_vendas->num_rows > 0): ?>

                                <?php while ($venda = $ultimas_vendas->fetch_assoc()): ?>

                                    <tr>

                                        <td>

    <a
        href="../vendas/visualizar.php?id=<?php echo $venda["id_venda"]; ?>"
        class="text-decoration-none fw-bold"
    >

        #<?php
        echo $venda["id_venda"];
        ?>

    </a>

</td>


                                        <td>

                                            <?php

                                            echo $venda["cliente"]
                                                ? htmlspecialchars($venda["cliente"])
                                                : "Cliente não identificado";

                                            ?>

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

                                            <strong>

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

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="4"
                                        class="text-center text-muted py-4"
                                    >

                                        Nenhuma venda registrada.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>
        <!-- STOCK BAIXO -->

    <div class="col-lg-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <h5 class="mb-4">
                    Produtos com stock baixo
                </h5>


                <?php if ($produtos_baixos && $produtos_baixos->num_rows > 0): ?>

                    <?php while ($produto = $produtos_baixos->fetch_assoc()): ?>

                        <div class="border-bottom pb-3 mb-3">

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $produto["nome"]
                                );
                                ?>

                            </strong>

                            <div class="text-muted small">

                                Disponível:
                                <?php echo $produto["quantidade"]; ?>

                                |

                                Mínimo:
                                <?php echo $produto["estoque_minimo"]; ?>

                            </div>

                        </div>

                    <?php endwhile; ?>


                <?php else: ?>

                    <div class="text-center text-muted py-4">

                        <div class="fs-3 mb-2">
                            📦
                        </div>

                        Nenhum produto com stock baixo.

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>