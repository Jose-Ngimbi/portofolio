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

$erro = "";


// ==============================
// BUSCAR CATEGORIAS
// ==============================

$sql_categorias = "SELECT id_categoria, nome
                   FROM categorias
                   ORDER BY nome ASC";

$categorias = $conn->query($sql_categorias);


// ==============================
// CADASTRAR PRODUTO
// ==============================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco_compra = $_POST["preco_compra"] ?? "";
    $preco_venda = $_POST["preco_venda"] ?? "";
    $quantidade = $_POST["quantidade"] ?? "";
    $estoque_minimo = $_POST["estoque_minimo"] ?? "";
    $id_categoria = $_POST["id_categoria"] ?? "";
    $status = $_POST["status"] ?? "ativo";


    // ==============================
    // VALIDAÇÕES
    // ==============================

    if ($nome === "") {

        $erro = "O nome do produto é obrigatório.";

    } elseif (!is_numeric($preco_compra) || $preco_compra < 0) {

        $erro = "O preço de compra é inválido.";

    } elseif (!is_numeric($preco_venda) || $preco_venda < 0) {

        $erro = "O preço de venda é inválido.";

    } elseif ($preco_venda < $preco_compra) {

        $erro = "O preço de venda não pode ser menor que o preço de compra.";

    } elseif (!filter_var($quantidade, FILTER_VALIDATE_INT) || $quantidade < 0) {

        $erro = "A quantidade deve ser um número inteiro igual ou superior a zero.";

    } elseif (!filter_var($estoque_minimo, FILTER_VALIDATE_INT) || $estoque_minimo < 0) {

        $erro = "O estoque mínimo deve ser um número inteiro igual ou superior a zero.";

    } elseif (
        $status !== "ativo" &&
        $status !== "inativo"
    ) {

        $erro = "Status inválido.";

    } else {

        // Se nenhuma categoria foi selecionada
        if ($id_categoria === "") {

            $id_categoria = null;

        } else {

            $id_categoria = (int) $id_categoria;
        }


        // ==============================
        // INSERIR
        // ==============================

        $sql = "INSERT INTO produtos
                (
                    nome,
                    descricao,
                    preco_compra,
                    preco_venda,
                    quantidade,
                    estoque_minimo,
                    id_categoria,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $erro = "Erro ao preparar o cadastro.";

        } else {

            $stmt->bind_param(
                "ssddiiis",
                $nome,
                $descricao,
                $preco_compra,
                $preco_venda,
                $quantidade,
                $estoque_minimo,
                $id_categoria,
                $status
            );


            if ($stmt->execute()) {

                header(
                    "Location: index.php?sucesso=Produto cadastrado com sucesso!"
                );

                exit;

            } else {

                $erro = "Erro ao cadastrar o produto.";
            }

            $stmt->close();
        }
    }
}

?>

<main class="main">

    <header class="topbar">

        <h1>Novo Produto</h1>

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
                Adicionar produto
            </h2>

            <p class="text-muted">
                Cadastre um novo produto no sistema.
            </p>

        </div>


        <?php if ($erro !== ""): ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($erro); ?>
            </div>

        <?php endif; ?>


        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <form method="POST">

                    <div class="row g-4">


                        <!-- NOME -->

                        <div class="col-md-6">

                            <label
                                for="nome"
                                class="form-label"
                            >
                                Nome do produto *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nome"
                                name="nome"
                                maxlength="150"
                                required
                                value="<?php echo htmlspecialchars($_POST["nome"] ?? ""); ?>"
                            >

                        </div>


                        <!-- CATEGORIA -->

                        <div class="col-md-6">

                            <label
                                for="id_categoria"
                                class="form-label"
                            >
                                Categoria
                            </label>

                            <select
                                class="form-select"
                                id="id_categoria"
                                name="id_categoria"
                            >

                                <option value="">
                                    Sem categoria
                                </option>

                                <?php if ($categorias): ?>

                                    <?php while ($categoria = $categorias->fetch_assoc()): ?>

                                        <option
                                            value="<?php echo $categoria["id_categoria"]; ?>"
                                            <?php
                                            echo (
                                                ($_POST["id_categoria"] ?? "") ==
                                                $categoria["id_categoria"]
                                            )
                                                ? "selected"
                                                : "";
                                            ?>
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $categoria["nome"]
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- DESCRIÇÃO -->

                        <div class="col-12">

                            <label
                                for="descricao"
                                class="form-label"
                            >
                                Descrição
                            </label>

                            <textarea
                                class="form-control"
                                id="descricao"
                                name="descricao"
                                rows="4"
                            ><?php echo htmlspecialchars($_POST["descricao"] ?? ""); ?></textarea>

                        </div>


                        <!-- PREÇO DE COMPRA -->

                        <div class="col-md-6">

                            <label
                                for="preco_compra"
                                class="form-label"
                            >
                                Preço de compra *
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="preco_compra"
                                name="preco_compra"
                                min="0"
                                step="0.01"
                                required
                                value="<?php echo htmlspecialchars($_POST["preco_compra"] ?? ""); ?>"
                            >

                        </div>


                        <!-- PREÇO DE VENDA -->

                        <div class="col-md-6">

                            <label
                                for="preco_venda"
                                class="form-label"
                            >
                                Preço de venda *
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="preco_venda"
                                name="preco_venda"
                                min="0"
                                step="0.01"
                                required
                                value="<?php echo htmlspecialchars($_POST["preco_venda"] ?? ""); ?>"
                            >

                        </div>


                        <!-- QUANTIDADE -->

                        <div class="col-md-6">

                            <label
                                for="quantidade"
                                class="form-label"
                            >
                                Quantidade em estoque *
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="quantidade"
                                name="quantidade"
                                min="0"
                                step="1"
                                required
                                value="<?php echo htmlspecialchars($_POST["quantidade"] ?? "0"); ?>"
                            >

                        </div>


                        <!-- ESTOQUE MÍNIMO -->

                        <div class="col-md-6">

                            <label
                                for="estoque_minimo"
                                class="form-label"
                            >
                                Estoque mínimo *
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="estoque_minimo"
                                name="estoque_minimo"
                                min="0"
                                step="1"
                                required
                                value="<?php echo htmlspecialchars($_POST["estoque_minimo"] ?? "5"); ?>"
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Status
                            </label>

                            <select
                                class="form-select"
                                id="status"
                                name="status"
                            >

                                <option
                                    value="ativo"
                                    <?php
                                    echo (
                                        ($_POST["status"] ?? "ativo") === "ativo"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Ativo
                                </option>

                                <option
                                    value="inativo"
                                    <?php
                                    echo (
                                        ($_POST["status"] ?? "") === "inativo"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Inativo
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="mt-4 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Cadastrar produto
                        </button>

                        <a
                            href="index.php"
                            class="btn btn-light"
                        >
                            Cancelar
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>