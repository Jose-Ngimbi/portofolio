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


// ==========================================
// PERÍODO
// ==========================================

$periodo = $_GET["periodo"] ?? "mes";

$data_inicio = date("Y-m-01");
$data_fim = date("Y-m-d");


if ($periodo === "hoje") {

    $data_inicio = date("Y-m-d");
    $data_fim = date("Y-m-d");

}


elseif ($periodo === "ontem") {

    $data_inicio = date(
        "Y-m-d",
        strtotime("-1 day")
    );

    $data_fim = $data_inicio;

}


elseif ($periodo === "7dias") {

    $data_inicio = date(
        "Y-m-d",
        strtotime("-6 days")
    );

    $data_fim = date("Y-m-d");

}


elseif ($periodo === "ano") {

    $data_inicio = date("Y-01-01");
    $data_fim = date("Y-m-d");

}


// ==========================================
// RESUMO DAS VENDAS
// ==========================================

$sql_resumo = "
    SELECT

        COUNT(*) AS quantidade_vendas,

        COALESCE(SUM(subtotal), 0) AS subtotal,

        COALESCE(SUM(desconto), 0) AS desconto,

        COALESCE(SUM(total), 0) AS total

    FROM vendas

    WHERE DATE(data_venda)
    BETWEEN ? AND ?
";


$stmt = $conn->prepare($sql_resumo);

$stmt->bind_param(
    "ss",
    $data_inicio,
    $data_fim
);

$stmt->execute();

$resumo = $stmt->get_result()->fetch_assoc();

$stmt->close();


$quantidade_vendas =
    $resumo["quantidade_vendas"] ?? 0;

$subtotal =
    $resumo["subtotal"] ?? 0;

$desconto =
    $resumo["desconto"] ?? 0;

$total_vendas =
    $resumo["total"] ?? 0;

// ==========================================
// VENDAS POR DIA
// ==========================================

$sql_vendas_dia = "
    SELECT
        DATE(data_venda) AS dia,
        COALESCE(SUM(total), 0) AS total
    FROM vendas
    WHERE DATE(data_venda) BETWEEN ? AND ?
    GROUP BY DATE(data_venda)
    ORDER BY dia ASC
";

$stmt = $conn->prepare($sql_vendas_dia);

$stmt->bind_param(
    "ss",
    $data_inicio,
    $data_fim
);

$stmt->execute();

$resultado_vendas_dia = $stmt->get_result();

$vendas_por_dia = [];

while ($venda = $resultado_vendas_dia->fetch_assoc()) {

    $vendas_por_dia[$venda["dia"]] =
        (float) $venda["total"];

}

$stmt->close();


// ==========================================
// CRIAR TODOS OS DIAS DO PERÍODO
// ==========================================

$dados_grafico = [];

$data_atual = new DateTime($data_inicio);
$data_final = new DateTime($data_fim);

while ($data_atual <= $data_final) {

    $dia = $data_atual->format("Y-m-d");

    $dados_grafico[] = [

        "dia" => $data_atual->format("d/m"),

        "total" => $vendas_por_dia[$dia] ?? 0

    ];

    $data_atual->modify("+1 day");
}


// ==========================================
// PRODUTOS VENDIDOS
// ==========================================

$sql_produtos = "
    SELECT
        COALESCE(SUM(vi.quantidade), 0) AS total

    FROM venda_itens vi

    INNER JOIN vendas v
        ON vi.id_venda = v.id_venda

    WHERE DATE(v.data_venda)
    BETWEEN ? AND ?
";


$stmt = $conn->prepare($sql_produtos);

$stmt->bind_param(
    "ss",
    $data_inicio,
    $data_fim
);

$stmt->execute();

$produtos_vendidos =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


$total_produtos_vendidos =
    $produtos_vendidos["total"] ?? 0;


// ==========================================
// FORMA DE PAGAMENTO
// ==========================================

$sql_pagamentos = "
    SELECT
        forma_pagamento,
        COUNT(*) AS quantidade,
        COALESCE(SUM(total), 0) AS total

    FROM vendas

    WHERE DATE(data_venda)
    BETWEEN ? AND ?

    GROUP BY forma_pagamento

    ORDER BY total DESC
";


$stmt = $conn->prepare($sql_pagamentos);

$stmt->bind_param(
    "ss",
    $data_inicio,
    $data_fim
);

$stmt->execute();

$pagamentos =
    $stmt->get_result();

$stmt->close();


// ==========================================
// PRODUTOS MAIS VENDIDOS
// ==========================================

$sql_top_produtos = "
    SELECT

        p.nome,

        SUM(vi.quantidade) AS quantidade,

        SUM(vi.subtotal) AS total

    FROM venda_itens vi

    INNER JOIN vendas v
        ON vi.id_venda = v.id_venda

    INNER JOIN produtos p
        ON vi.id_produto = p.id_produto

    WHERE DATE(v.data_venda)
    BETWEEN ? AND ?

    GROUP BY p.id_produto, p.nome

    ORDER BY quantidade DESC

    LIMIT 5
";


$stmt = $conn->prepare($sql_top_produtos);

$stmt->bind_param(
    "ss",
    $data_inicio,
    $data_fim
);

$stmt->execute();

$top_produtos =
    $stmt->get_result();

$stmt->close();


// ==========================================
// CLIENTES QUE MAIS COMPRARAM
// ==========================================

$sql_top_clientes = "
    SELECT

        COALESCE(c.nome, 'Cliente não identificado')
        AS cliente,

        COUNT(v.id_venda) AS vendas,

        COALESCE(SUM(v.total), 0) AS total

    FROM vendas v

    LEFT JOIN clientes c
        ON v.id_cliente = c.id_cliente

    WHERE DATE(v.data_venda)
    BETWEEN ? AND ?

    GROUP BY v.id_cliente, c.nome

    ORDER BY total DESC

    LIMIT 5
";


$stmt = $conn->prepare($sql_top_clientes);

$stmt->bind_param(
    "ss",
    $data_inicio,
    $data_fim
);

$stmt->execute();

$top_clientes =
    $stmt->get_result();

$stmt->close();

?>

<main class="main">

    <header class="topbar">

        <h1>Relatórios</h1>

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
        <!-- CABEÇALHO PARA IMPRESSÃO -->

<div class="cabecalho-impressao">

    <h1>BizFlow</h1>

    <p>
        Sistema de Gestão Empresarial
    </p>

    <h2>
        Relatório de Vendas
    </h2>

    <div class="info-relatorio">

        <span>
            <strong>Período:</strong>

            <?php

            if ($periodo === "hoje") {

                echo "Hoje";

            } elseif ($periodo === "ontem") {

                echo "Ontem";

            } elseif ($periodo === "7dias") {

                echo "Últimos 7 dias";

            } elseif ($periodo === "ano") {

                echo "Este ano";

            } else {

                echo "Este mês";

            }

            ?>

        </span>


        <span>

            <strong>Data de geração:</strong>

            <?php

            echo date("d/m/Y H:i");

            ?>

        </span>

    </div>

</div>


        <!-- CABEÇALHO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-semibold mb-1">
                    Relatórios
                </h2>
                <p class="periodo-relatorio">

    Período:

    <?php

    if ($periodo === "hoje") {

        echo "Hoje";

    } elseif ($periodo === "ontem") {

        echo "Ontem";

    } elseif ($periodo === "7dias") {

        echo "Últimos 7 dias";

    } elseif ($periodo === "ano") {

        echo "Este ano";

    } else {

        echo "Este mês";

    }

    ?>

</p>

                <p class="text-muted mb-0">
                    Analise o desempenho do seu negócio.
                </p>

            </div>


            <!-- FILTRO -->
       
    <?php if (
    $_SESSION["nivel"] === "administrador" ||
    $_SESSION["nivel"] === "gerente"
          ): ?>

    <button 
    type="button"
    class="btn btn-outline-secondary"
    onclick="window.print()">
        🖨️ Imprimir
    </button>

     <?php endif; ?>

            <form method="GET">

                <div class="d-flex gap-2">

                    <select
                        name="periodo"
                        class="form-select"
                    >

                        <option
                            value="hoje"
                            <?php
                            echo $periodo === "hoje"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Hoje
                        </option>

                        <option
                            value="ontem"
                            <?php
                            echo $periodo === "ontem"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Ontem
                        </option>

                        <option
                            value="7dias"
                            <?php
                            echo $periodo === "7dias"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Últimos 7 dias
                        </option>

                        <option
                            value="mes"
                            <?php
                            echo $periodo === "mes"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Este mês
                        </option>

                        <option
                            value="ano"
                            <?php
                            echo $periodo === "ano"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Este ano
                        </option>

                    </select>

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        Filtrar
                    </button>

                </div>

            </form>

        </div>

        <!-- RESUMO -->

        <div class="row g-4 mb-4">


            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-title">
                        Vendas
                    </div>

                    <div class="stat-value">

                        <?php
                        echo $quantidade_vendas;
                        ?>

                    </div>

                </div>

            </div>

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-title">
                        Produtos vendidos
                    </div>

                    <div class="stat-value">

                        <?php
                        echo $total_produtos_vendidos;
                        ?>

                    </div>

                </div>

            </div>


            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-title">
                        Descontos
                    </div>

                    <div class="stat-value">

                        <?php

                        echo number_format(
                            $desconto,
                            2,
                            ",",
                            "."
                        );

                        ?>

                        Kz

                    </div>

                </div>

            </div>


            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-title">
                        Faturamento
                    </div>

                    <!-- GRÁFICO DE VENDAS -->

<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <h5 class="mb-4">
            Vendas por dia
        </h5>

        <div style="height: 350px;">

            <canvas id="graficoVendas"></canvas>

        </div>

    </div>

</div>


                    <div class="stat-value">

                        <?php

                        echo number_format(
                            $total_vendas,
                            2,
                            ",",
                            "."
                        );

                        ?>

                        Kz

                    </div>

                </div>

            </div>

        </div>


        <div class="row g-4">


            <!-- PRODUTOS MAIS VENDIDOS -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <h5 class="mb-4">
                            Produtos mais vendidos
                        </h5>


                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>Produto</th>
                                        <th>Qtd.</th>
                                        <th>Total</th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php if ($top_produtos->num_rows > 0): ?>

                                    <?php while ($produto = $top_produtos->fetch_assoc()): ?>

                                        <tr>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $produto["nome"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo $produto["quantidade"];
                                                ?>
                                            </td>

                                            <td>

                                                <?php

                                                echo number_format(
                                                    $produto["total"],
                                                    2,
                                                    ",",
                                                    "."
                                                );

                                                ?>

                                                Kz

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="text-center text-muted"
                                        >
                                            Nenhuma venda no período.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- CLIENTES -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <h5 class="mb-4">
                            Clientes que mais compraram
                        </h5>


                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>Cliente</th>
                                        <th>Vendas</th>
                                        <th>Total</th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php if ($top_clientes->num_rows > 0): ?>

                                    <?php while ($cliente = $top_clientes->fetch_assoc()): ?>

                                        <tr>

                                            <td>

                                                <?php

                                                echo htmlspecialchars(
                                                    $cliente["cliente"]
                                                );

                                                ?>

                                            </td>

                                            <td>

                                                <?php
                                                echo $cliente["vendas"];
                                                ?>

                                            </td>

                                            <td>

                                                <?php

                                                echo number_format(
                                                    $cliente["total"],
                                                    2,
                                                    ",",
                                                    "."
                                                );

                                                ?>

                                                Kz

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="text-center text-muted"
                                        >
                                            Nenhuma venda no período.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- FORMAS DE PAGAMENTO -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <h5 class="mb-4">
                            Formas de pagamento
                        </h5>


                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>Forma</th>
                                        <th>Vendas</th>
                                        <th>Total</th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php if ($pagamentos->num_rows > 0): ?>

                                    <?php while ($pagamento = $pagamentos->fetch_assoc()): ?>

                                        <tr>

                                            <td>

                                                <?php

                                                echo ucfirst(
                                                    $pagamento["forma_pagamento"]
                                                );

                                                ?>

                                            </td>

                                            <td>

                                                <?php
                                                echo $pagamento["quantidade"];
                                                ?>

                                            </td>

                                            <td>

                                                <?php

                                                echo number_format(
                                                    $pagamento["total"],
                                                    2,
                                                    ",",
                                                    "."
                                                );

                                                ?>

                                                Kz

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="text-center text-muted"
                                        >
                                            Nenhuma venda no período.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>
                                       
            </div>
            <!-- GRÁFICO DE FORMAS DE PAGAMENTO -->

<div class="col-lg-6">

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <h5 class="mb-4">
                Distribuição por forma de pagamento
            </h5>

            <div style="height: 350px;">

                <canvas id="graficoPagamentos"></canvas>

            </div>

        </div>

    </div>

</div>
        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const dadosVendas = <?php
echo json_encode($dados_grafico);
?>;

const labels = dadosVendas.map(
    item => item.dia
);

const valores = dadosVendas.map(
    item => item.total
);


new Chart(
    document.getElementById("graficoVendas"),
    {

        type: "line",

        data: {

            labels: labels,

            datasets: [

                {

                    label: "Vendas",

                    data: valores,

                    tension: 0.4,

                    fill: true

                }

            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    display: false
                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        callback: function(value) {

                            return value.toLocaleString(
                                "pt-PT"
                            ) + " Kz";

                        }

                    }

                }

            }

        }

    }
);

</script>
<script>

const dadosPagamentos = [

<?php

$pagamentos->data_seek(0);

while ($pagamento = $pagamentos->fetch_assoc()) {

    echo json_encode([
        "forma" => ucfirst($pagamento["forma_pagamento"]),
        "total" => (float) $pagamento["total"]
    ]);

    echo ",";

}

?>

];


const labelsPagamentos = dadosPagamentos.map(
    item => item.forma
);

const valoresPagamentos = dadosPagamentos.map(
    item => item.total
);


new Chart(
    document.getElementById("graficoPagamentos"),
    {

        type: "doughnut",

        data: {

            labels: labelsPagamentos,

            datasets: [

                {

                    data: valoresPagamentos

                }

            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    position: "bottom"

                },

                tooltip: {

                    callbacks: {

                        label: function(context) {

                            const valor =
                                context.raw.toLocaleString(
                                    "pt-PT"
                                );

                            return valor + " Kz";

                        }

                    }

                }

            }

        }

    }
);

</script>

 <script>

   document.addEventListener("keydown", function(event) {

    <?php if (
        $_SESSION["nivel"] !== "administrador" &&
        $_SESSION["nivel"] !== "gerente"
    ): ?>

        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === "p"
        ) {

            event.preventDefault();

            alert("A impressão de relatórios não está disponível para funcionários.");

        }

    <?php endif; ?>

    });

 </script>
<style>
    .rodape-impressao {
    display: none;
 }
.periodo-relatorio {

    display: none;

}
.cabecalho-impressao {
    display: none;
}
@media print {
    .cabecalho-impressao {

    display: block;

    text-align: center;

    margin-bottom: 30px;

    padding-bottom: 20px;

    border-bottom: 2px solid #222;

}

.cabecalho-impressao h1 {

    margin: 0;

    font-size: 30px;

    font-weight: 700;

 }

.cabecalho-impressao p {

    margin: 2px 0 15px;

    color: #555;

 }

.cabecalho-impressao h2 {

    margin: 0 0 15px;

    font-size: 22px;

 }

.info-relatorio {

    display: flex;

    justify-content: center;

    gap: 40px;

    font-size: 13px;

 }


    /* ==========================================
       OCULTAR ELEMENTOS DA INTERFACE
       ========================================== */

    .sidebar,
    .topbar,
    .btn,
    form,
    nav,
    footer {

        display: none !important;

    }


    /* ==========================================
       CONFIGURAÇÃO DA PÁGINA
       ========================================== */

    body {

        background: white !important;
        margin: 0 !important;
        padding: 0 !important;

    }


    .main {

        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;

    }


    .content {

        padding: 25px !important;

    }


    /* ==========================================
       CABEÇALHO DO RELATÓRIO
       ========================================== */

    .content::before {

        content:
            "BIZFLOW\A"
            "Sistema de Gestão Empresarial\A\A"
            "RELATÓRIO DE VENDAS";

        white-space: pre;

        display: block;

        text-align: center;

        font-weight: 700;

        font-size: 24px;

        line-height: 1.5;

        margin-bottom: 25px;

        padding-bottom: 15px;

        border-bottom: 2px solid #222;

    }


    /* ==========================================
       CARDS
       ========================================== */

    .card,
    .stat-card {

        box-shadow: none !important;

        border: 1px solid #ddd !important;

        break-inside: avoid;

    }


    /* ==========================================
       TABELAS
       ========================================== */

    table {

        width: 100% !important;

    }


    /* ==========================================
       GRÁFICOS
       ========================================== */

    canvas {

        max-width: 100% !important;

    }


    /* ==========================================
       EVITAR CORTES
       ========================================== */

    .row {

        break-inside: avoid;

    }
    .periodo-relatorio {

    display: block !important;

    }
    .rodape-impressao {

    display: block;

    text-align: center;

    margin-top: 30px;

    padding-top: 15px;

    border-top: 1px solid #ddd;

    font-size: 12px;

    color: #666;

 }


}


</style>

<!-- RODAPÉ DO RELATÓRIO -->

<div class="rodape-impressao">

    Relatório gerado por José Ngimbi
    — Sistema de Gestão Empresarial

</div>

<?php

require_once "../includes/footer.php";

?>