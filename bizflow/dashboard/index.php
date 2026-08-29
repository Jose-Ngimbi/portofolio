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

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>