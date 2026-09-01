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


// Buscar categoria
$sql = "SELECT id_categoria, nome, descricao
        FROM categorias
        WHERE id_categoria = ?
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

$categoria = $resultado->fetch_assoc();

$stmt->close();


// Atualizar categoria
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");


    if ($nome === "") {

        $erro = "O nome da categoria é obrigatório.";

    } else {

        $sql = "UPDATE categorias
                SET nome = ?,
                    descricao = ?
                WHERE id_categoria = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $erro = "Erro ao preparar a atualização.";

        } else {

            $stmt->bind_param(
                "ssi",
                $nome,
                $descricao,
                $id
            );

            if ($stmt->execute()) {

                header(
                    "Location: index.php?sucesso=Categoria atualizada com sucesso!"
                );

                exit;

            } else {

                if ($stmt->errno == 1062) {

                    $erro = "Já existe uma categoria com este nome.";

                } else {

                    $erro = "Erro ao atualizar a categoria.";
                }
            }

            $stmt->close();
        }
    }
}

?>

<main class="main">

    <header class="topbar">

        <h1>Editar Categoria</h1>

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
                Editar categoria
            </h2>

            <p class="text-muted">
                Atualize os dados da categoria.
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

                    <div class="mb-4">

                        <label
                            for="nome"
                            class="form-label"
                        >
                            Nome da categoria *
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="nome"
                            name="nome"
                            maxlength="100"
                            required
                            value="<?php echo htmlspecialchars($_POST["nome"] ?? $categoria["nome"]); ?>"
                        >

                    </div>


                    <div class="mb-4">

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
                        ><?php echo htmlspecialchars($_POST["descricao"] ?? $categoria["descricao"] ?? ""); ?></textarea>

                    </div>


                    <div class="d-flex gap-2">

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