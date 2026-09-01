<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";

$erro = "";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}


// Buscar produto
$sql = "SELECT *
        FROM produtos
        WHERE id_produto = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;
}

$produto = $resultado->fetch_assoc();

$stmt->close();


// Buscar categorias
$sql_categorias = "SELECT id_categoria, nome
                   FROM categorias
                   ORDER BY nome ASC";

$categorias = $conn->query($sql_categorias);


// Atualizar produto
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco_compra = $_POST["preco_compra"] ?? "";
    $preco_venda = $_POST["preco_venda"] ?? "";
    $quantidade = $_POST["quantidade"] ?? "";
    $estoque_minimo = $_POST["estoque_minimo"] ?? "";
    $id_categoria = $_POST["id_categoria"] ?? "";
    $status = $_POST["status"] ?? "ativo";


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

        if ($id_categoria === "") {

            $id_categoria = null;

        } else {

            $id_categoria = (int) $id_categoria;
        }


        $sql = "UPDATE produtos
                SET nome = ?,
                    descricao = ?,
                    preco_compra = ?,
                    preco_venda = ?,
                    quantidade = ?,
                    estoque_minimo = ?,
                    id_categoria = ?,
                    status = ?
                WHERE id_produto = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $erro = "Erro ao preparar a atualização.";

        } else {

            $stmt->bind_param(
                "ssddiiisi",
                $nome,
                $descricao,
                $preco_compra,
                $preco_venda,
                $quantidade,
                $estoque_minimo,
                $id_categoria,
                $status,
                $id
            );


            if ($stmt->execute()) {

                header(
                    "Location: index.php?sucesso=Produto atualizado com sucesso!"
                );

                exit;

            } else {

                $erro = "Erro ao atualizar o produto.";
            }

            $stmt->close();
        }
    }
}

?>

<main class="main">

    <header class="topbar">

        <h1>Editar Produto</h1>

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
                Editar produto
            </h2>

            <p class="text-muted">
                Atualize os dados do produto.
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


                        <div class="col-md-6">

                            <label for="nome" class="form-label">
                                Nome do produto *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nome"
                                name="nome"
                                maxlength="150"
                                required
                                value="<?php echo htmlspecialchars($_POST["nome"] ?? $produto["nome"]); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="id_categoria" class="form-label">
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

                                <?php while ($categoria = $categorias->fetch_assoc()): ?>

                                    <option
                                        value="<?php echo $categoria["id_categoria"]; ?>"
                                        <?php

                                        $categoria_atual =
                                            $_POST["id_categoria"]
                                            ?? $produto["id_categoria"];

                                        echo (
                                            $categoria_atual ==
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

                            </select>

                        </div>


                        <div class="col-12">

                            <label for="descricao" class="form-label">
                                Descrição
                            </label>

                            <textarea
                                class="form-control"
                                id="descricao"
                                name="descricao"
                                rows="4"
                            ><?php echo htmlspecialchars($_POST["descricao"] ?? $produto["descricao"] ?? ""); ?></textarea>

                        </div>


                        <div class="col-md-6">

                            <label for="preco_compra" class="form-label">
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
                                value="<?php echo htmlspecialchars($_POST["preco_compra"] ?? $produto["preco_compra"]); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="preco_venda" class="form-label">
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
                                value="<?php echo htmlspecialchars($_POST["preco_venda"] ?? $produto["preco_venda"]); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="quantidade" class="form-label">
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
                                value="<?php echo htmlspecialchars($_POST["quantidade"] ?? $produto["quantidade"]); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="estoque_minimo" class="form-label">
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
                                value="<?php echo htmlspecialchars($_POST["estoque_minimo"] ?? $produto["estoque_minimo"]); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="status" class="form-label">
                                Status
                            </label>

                            <select
                                class="form-select"
                                id="status"
                                name="status"
                            >

                                <?php
                                $status_atual =
                                    $_POST["status"]
                                    ?? $produto["status"];
                                ?>

                                <option
                                    value="ativo"
                                    <?php echo $status_atual === "ativo" ? "selected" : ""; ?>
                                >
                                    Ativo
                                </option>

                                <option
                                    value="inativo"
                                    <?php echo $status_atual === "inativo" ? "selected" : ""; ?>
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
                            Guardar alterações
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