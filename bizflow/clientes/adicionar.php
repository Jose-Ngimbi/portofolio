<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $endereco = trim($_POST["endereco"] ?? "");

    if ($nome === "") {

        $erro = "O nome do cliente é obrigatório.";

    } else {

        $sql = "INSERT INTO clientes (nome, telefone, email, endereco)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $erro = "Erro ao preparar o cadastro.";

        } else {

            $stmt->bind_param(
                "ssss",
                $nome,
                $telefone,
                $email,
                $endereco
            );

            if ($stmt->execute()) {

                header("Location: index.php?sucesso=Cliente cadastrado com sucesso!");
                exit;

            } else {

                $erro = "Erro ao cadastrar o cliente.";
            }

            $stmt->close();
        }
    }
}

?>

<main class="main">

    <header class="topbar">

        <h1>Novo Cliente</h1>

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
                Adicionar cliente
            </h2>

            <p class="text-muted">
                Preencha os dados do novo cliente.
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
                                Nome completo *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nome"
                                name="nome"
                                required
                                maxlength="150"
                                value="<?php echo htmlspecialchars($_POST["nome"] ?? ""); ?>"
                            >

                        </div>


                        <!-- TELEFONE -->

                        <div class="col-md-6">

                            <label
                                for="telefone"
                                class="form-label"
                            >
                                Telefone
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="telefone"
                                name="telefone"
                                maxlength="30"
                                value="<?php echo htmlspecialchars($_POST["telefone"] ?? ""); ?>"
                            >

                        </div>


                        <!-- EMAIL -->

                        <div class="col-md-6">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Email
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                maxlength="150"
                                value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                            >

                        </div>


                        <!-- ENDEREÇO -->

                        <div class="col-md-6">

                            <label
                                for="endereco"
                                class="form-label"
                            >
                                Endereço
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="endereco"
                                name="endereco"
                                maxlength="255"
                                value="<?php echo htmlspecialchars($_POST["endereco"] ?? ""); ?>"
                            >

                        </div>


                    </div>


                    <div class="mt-4 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Cadastrar cliente
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