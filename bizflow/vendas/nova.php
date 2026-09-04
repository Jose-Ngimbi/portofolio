<?php

require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente",
    "funcionario"
]);

require_once "../includes/header.php";
require_once "../includes/sidebar.php";

$erro = "";


// ==========================================
// BUSCAR CLIENTES
// ==========================================

$sql_clientes = "SELECT id_cliente, nome
                 FROM clientes
                 ORDER BY nome ASC";

$clientes = $conn->query($sql_clientes);


// ==========================================
// BUSCAR PRODUTOS ATIVOS
// ==========================================

$sql_produtos = "SELECT
                    id_produto,
                    nome,
                    preco_venda,
                    quantidade
                 FROM produtos
                 WHERE status = 'ativo'
                 ORDER BY nome ASC";

$produtos = $conn->query($sql_produtos);


// ==========================================
// FINALIZAR VENDA
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_cliente = $_POST["id_cliente"] ?? "";
    $forma_pagamento = $_POST["forma_pagamento"] ?? "";
    $desconto = $_POST["desconto"] ?? 0;

    $produtos_post = $_POST["produto"] ?? [];
    $quantidades_post = $_POST["quantidade"] ?? [];


    // ==========================================
    // VALIDAÇÕES BÁSICAS
    // ==========================================

    if ($id_cliente !== "" && !filter_var($id_cliente, FILTER_VALIDATE_INT)) {

        $erro = "Cliente inválido.";

    } elseif (
        !in_array(
            $forma_pagamento,
            [
                "dinheiro",
                "transferencia",
                "multicaixa",
                "cartao"
            ],
            true
        )
    ) {

        $erro = "Forma de pagamento inválida.";

    } elseif (!is_numeric($desconto) || $desconto < 0) {

        $erro = "Desconto inválido.";

    } elseif (empty($produtos_post)) {

        $erro = "Adicione pelo menos um produto à venda.";

    } else {

        $desconto = (float) $desconto;

        $itens = [];
        $subtotal = 0;


        // ==========================================
        // PROCESSAR PRODUTOS
        // ==========================================

        foreach ($produtos_post as $indice => $id_produto) {

            $id_produto = filter_var(
                $id_produto,
                FILTER_VALIDATE_INT
            );

            $quantidade = $quantidades_post[$indice] ?? 0;

            $quantidade = filter_var(
                $quantidade,
                FILTER_VALIDATE_INT
            );


            if (!$id_produto || !$quantidade || $quantidade <= 0) {

                $erro = "Produto ou quantidade inválida.";
                break;
            }


            // Buscar produto na BD
            $sql = "SELECT
                        id_produto,
                        nome,
                        preco_venda,
                        quantidade
                    FROM produtos
                    WHERE id_produto = ?
                      AND status = 'ativo'
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_produto);
            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows !== 1) {

                $stmt->close();

                $erro = "Um dos produtos selecionados não existe.";
                break;
            }

            $produto = $resultado->fetch_assoc();

            $stmt->close();


            // ==========================================
            // VERIFICAR ESTOQUE
            // ==========================================

            if ($quantidade > $produto["quantidade"]) {

                $erro =
                    "Estoque insuficiente para o produto: "
                    . $produto["nome"];

                break;
            }


            // ==========================================
            // CALCULAR SUBTOTAL DO ITEM
            // ==========================================

            $preco_unitario = (float) $produto["preco_venda"];

            $subtotal_item =
                $preco_unitario * $quantidade;

            $subtotal += $subtotal_item;


            $itens[] = [

                "id_produto" =>
                    $id_produto,

                "quantidade" =>
                    $quantidade,

                "preco_unitario" =>
                    $preco_unitario,

                "subtotal" =>
                    $subtotal_item

            ];
        }


        // ==========================================
        // CALCULAR TOTAL
        // ==========================================

        if ($erro === "") {

            if ($desconto > $subtotal) {

                $erro =
                    "O desconto não pode ser maior que o subtotal.";

            } else {

                $total = $subtotal - $desconto;
            }
        }


        // ==========================================
        // GRAVAR VENDA
        // ==========================================

        if ($erro === "") {

            try {

                $conn->begin_transaction();


                // ----------------------------------
                // 1. CRIAR VENDA
                // ----------------------------------

                $sql = "INSERT INTO vendas
                        (
                            id_cliente,
                            id_usuario,
                            subtotal,
                            desconto,
                            total,
                            forma_pagamento
                        )
                        VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                $id_usuario =
                    $_SESSION["id_usuario"];

                $stmt->bind_param(
                    "iiddds",
                    $id_cliente,
                    $id_usuario,
                    $subtotal,
                    $desconto,
                    $total,
                    $forma_pagamento
                );

                $stmt->execute();

                $id_venda =
                    $conn->insert_id;

                $stmt->close();


                // ----------------------------------
                // 2. INSERIR ITENS
                // ----------------------------------

                foreach ($itens as $item) {

                    $sql = "INSERT INTO venda_itens
                            (
                                id_venda,
                                id_produto,
                                quantidade,
                                preco_unitario,
                                subtotal
                            )
                            VALUES (?, ?, ?, ?, ?)";

                    $stmt =
                        $conn->prepare($sql);

                    $stmt->bind_param(
                        "iiidd",
                        $id_venda,
                        $item["id_produto"],
                        $item["quantidade"],
                        $item["preco_unitario"],
                        $item["subtotal"]
                    );

                    $stmt->execute();

                    $stmt->close();


                    // ----------------------------------
                    // 3. BAIXAR ESTOQUE
                    // ----------------------------------

                    $sql = "UPDATE produtos
                            SET quantidade =
                                quantidade - ?
                            WHERE id_produto = ?
                              AND quantidade >= ?";

                    $stmt =
                        $conn->prepare($sql);

                    $stmt->bind_param(
                        "iii",
                        $item["quantidade"],
                        $item["id_produto"],
                        $item["quantidade"]
                    );

                    $stmt->execute();


                    if ($stmt->affected_rows !== 1) {

                        throw new Exception(
                            "Não foi possível atualizar o estoque."
                        );
                    }

                    $stmt->close();
                }


                // ----------------------------------
                // 4. REGISTRAR NO CAIXA
                // ----------------------------------

                $descricao =
                    "Venda #" . $id_venda;

                $tipo = "entrada";

                $sql = "INSERT INTO movimentos_caixa
                        (
                            id_usuario,
                            tipo,
                            valor,
                            descricao
                        )
                        VALUES (?, ?, ?, ?)";

                $stmt =
                    $conn->prepare($sql);

                $stmt->bind_param(
                    "isds",
                    $id_usuario,
                    $tipo,
                    $total,
                    $descricao
                );

                $stmt->execute();

                $stmt->close();


                // ----------------------------------
                // 5. CONFIRMAR
                // ----------------------------------

                $conn->commit();


                header(
                    "Location: visualizar.php?id="
                    . $id_venda
                    . "&sucesso=Venda registrada com sucesso!"
                );

                exit;


            } catch (Exception $e) {

                $conn->rollback();

                $erro =
                    "Não foi possível registrar a venda: "
                    . $e->getMessage();
            }
        }
    }
}

?>

<main class="main">

    <header class="topbar">

        <h1>Nova Venda</h1>

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

        <div class="mb-4">

            <h2 class="fw-semibold">
                Nova Venda
            </h2>

            <p class="text-muted">
                Registre uma nova venda para um cliente.
            </p>

        </div>


        <?php if ($erro !== ""): ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($erro); ?>
            </div>

        <?php endif; ?>


        <form method="POST" id="formVenda">

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <h5 class="mb-4">
                        Dados da venda
                    </h5>


                    <div class="row g-4">

                        <!-- CLIENTE -->

                        <div class="col-md-6">

                            <label
                                for="id_cliente"
                                class="form-label"
                            >
                                Cliente
                            </label>

                            <select
                                name="id_cliente"
                                id="id_cliente"
                                class="form-select"
                            >

                                <option value="">
                                    Venda sem cliente
                                </option>

                                <?php if ($clientes): ?>

                                    <?php while ($cliente = $clientes->fetch_assoc()): ?>

                                        <option
                                            value="<?php echo $cliente["id_cliente"]; ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $cliente["nome"]
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- PAGAMENTO -->

                        <div class="col-md-6">

                            <label
                                for="forma_pagamento"
                                class="form-label"
                            >
                                Forma de pagamento *
                            </label>

                            <select
                                name="forma_pagamento"
                                id="forma_pagamento"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecione
                                </option>

                                <option value="dinheiro">
                                    Dinheiro
                                </option>

                                <option value="transferencia">
                                    Transferência
                                </option>

                                <option value="multicaixa">
                                    Multicaixa
                                </option>

                                <option value="cartao">
                                    Cartão
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

            </div>


            <!-- PRODUTOS -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h5 class="mb-0">
                            Produtos
                        </h5>

                        <button
                            type="button"
                            class="btn btn-outline-success"
                            onclick="adicionarProduto()"
                        >
                            + Adicionar produto
                        </button>

                    </div>


                    <div id="produtosContainer">

                        <div class="produto-item row g-3 mb-3">

                            <div class="col-md-7">

                                <label class="form-label">
                                    Produto
                                </label>

                                <select
                                    name="produto[]"
                                    class="form-select produto-select"
                                    required
                                >

                                    <option value="">
                                        Selecione um produto
                                    </option>

                                    <?php

                                    if ($produtos):

                                        while (
                                            $produto =
                                            $produtos->fetch_assoc()
                                        ):

                                    ?>

                                        <option
                                            value="<?php echo $produto["id_produto"]; ?>"
                                            data-preco="<?php echo $produto["preco_venda"]; ?>"
                                            data-estoque="<?php echo $produto["quantidade"]; ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $produto["nome"]
                                            );

                                            echo " — ";

                                            echo number_format(
                                                $produto["preco_venda"],
                                                2,
                                                ",",
                                                "."
                                            );

                                            echo " Kz";
                                            ?>

                                        </option>

                                    <?php endwhile; endif; ?>

                                </select>

                                <small class="text-muted estoque-info"></small>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    Quantidade
                                </label>

                                <input
                                    type="number"
                                    name="quantidade[]"
                                    class="form-control quantidade"
                                    min="1"
                                    value="1"
                                    required
                                >

                            </div>


                            <div class="col-md-2 d-flex align-items-end">

                                <button
                                    type="button"
                                    class="btn btn-outline-danger w-100 remover-produto"
                                    onclick="removerProduto(this)"
                                >
                                    Remover
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- RESUMO -->

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h5 class="mb-4">
                        Resumo
                    </h5>


                    <div class="row justify-content-end">

                        <div class="col-md-5">

                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    Subtotal
                                </span>

                                <strong id="subtotal">
                                    0,00 Kz
                                </strong>

                            </div>


                            <div class="mb-3">

                                <label
                                    for="desconto"
                                    class="form-label"
                                >
                                    Desconto
                                </label>

                                <input
                                    type="number"
                                    name="desconto"
                                    id="desconto"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    value="0"
                                >

                            </div>


                            <div class="d-flex justify-content-between fs-5">

                                <strong>
                                    TOTAL
                                </strong>

                                <strong id="total">
                                    0,00 Kz
                                </strong>

                            </div>


                            <button
                                type="submit"
                                class="btn btn-success w-100 mt-4"
                            >
                                Finalizar venda
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </section>

</main>


<script>

function adicionarProduto() {

    const container =
        document.getElementById("produtosContainer");

    const primeiro =
        document.querySelector(".produto-item");

    const novo =
        primeiro.cloneNode(true);


    novo.querySelector("select").value = "";

    novo.querySelector("input").value = 1;

    novo.querySelector(".estoque-info").textContent = "";

    container.appendChild(novo);

    atualizarEventos();
    calcularTotal();
}


function removerProduto(botao) {

    const itens =
        document.querySelectorAll(".produto-item");

    if (itens.length <= 1) {

        alert(
            "A venda precisa ter pelo menos um produto."
        );

        return;
    }

    botao
        .closest(".produto-item")
        .remove();

    calcularTotal();
}


function atualizarEventos() {

    document
        .querySelectorAll(".produto-select")
        .forEach(function(select) {

            select.onchange = function() {

                const option =
                    this.options[this.selectedIndex];

                const estoque =
                    option.dataset.estoque;

                const info =
                    this.closest(".produto-item")
                        .querySelector(".estoque-info");

                if (estoque !== undefined) {

                    info.textContent =
                        "Estoque disponível: "
                        + estoque;

                } else {

                    info.textContent = "";
                }

                calcularTotal();
            };

        });


    document
        .querySelectorAll(".quantidade")
        .forEach(function(input) {

            input.oninput = function() {

                calcularTotal();
            };

        });

}


function calcularTotal() {

    let subtotal = 0;


    document
        .querySelectorAll(".produto-item")
        .forEach(function(item) {

            const select =
                item.querySelector(".produto-select");

            const quantidade =
                parseInt(
                    item.querySelector(".quantidade").value
                ) || 0;

            const option =
                select.options[
                    select.selectedIndex
                ];

            if (
                option &&
                option.dataset.preco
            ) {

                const preco =
                    parseFloat(
                        option.dataset.preco
                    );

                subtotal +=
                    preco * quantidade;
            }

        });


    const desconto =
        parseFloat(
            document.getElementById("desconto").value
        ) || 0;


    let total =
        subtotal - desconto;


    if (total < 0) {
        total = 0;
    }


    document.getElementById("subtotal")
        .textContent =
            subtotal.toLocaleString(
                "pt-PT",
                {
                    minimumFractionDigits: 2
                }
            ) + " Kz";


    document.getElementById("total")
        .textContent =
            total.toLocaleString(
                "pt-PT",
                {
                    minimumFractionDigits: 2
                }
            ) + " Kz";
}


document
    .getElementById("desconto")
    .addEventListener(
        "input",
        calcularTotal
    );


atualizarEventos();

calcularTotal();

</script>


<?php

require_once "../includes/footer.php";

?>