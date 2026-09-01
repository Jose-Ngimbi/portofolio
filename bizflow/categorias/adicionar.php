<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");


    if ($nome === "") {

        $erro = "O nome da categoria é obrigatório.";

    } else {

        $sql = "INSERT INTO categorias (nome, descricao)
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $erro = "Erro ao preparar o cadastro.";

        } else {

            $stmt->bind_param(
                "ss",
                $nome,
                $descricao
            );

            if ($stmt->execute()) {

                header(
                    "Location: index.php?sucesso=Categoria cadastrada com sucesso!"
                );

                exit;

            } else {

                if ($stmt->errno == 1062) {

                    $erro = "Esta categoria já existe.";

                } else {

                    $erro = "Erro ao cadastrar a categoria.";
                }
            }

            $stmt->close();
        }
    }
}

?>

<main class="main">

    <header class="topbar">

        <h1>Nova Categoria</h1>

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
                Adicionar categoria
            </h2>

            <p class="text-muted">
                Crie uma categoria para organizar os produtos.
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
                            value="<?php echo htmlspecialchars($_POST["nome"] ?? ""); ?>"
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
                        ><?php echo htmlspecialchars($_POST["descricao"] ?? ""); ?></textarea>

                    </div>


                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Cadastrar categoria
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