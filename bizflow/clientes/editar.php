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

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}


// Buscar cliente
$sql = "SELECT id_cliente, nome, telefone, email, endereco
        FROM clientes
        WHERE id_cliente = ?
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

$cliente = $resultado->fetch_assoc();

$stmt->close();


// Atualizar cliente
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $endereco = trim($_POST["endereco"] ?? "");


    if ($nome === "") {

        $erro = "O nome do cliente é obrigatório.";

    } else {

        $sql = "UPDATE clientes
                SET nome = ?,
                    telefone = ?,
                    email = ?,
                    endereco = ?
                WHERE id_cliente = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $erro = "Erro ao preparar a atualização.";

        } else {

            $stmt->bind_param(
                "ssssi",
                $nome,
                $telefone,
                $email,
                $endereco,
                $id
            );

            if ($stmt->execute()) {

                header("Location: index.php?sucesso=Cliente atualizado com sucesso!");
                exit;

            } else {

                $erro = "Erro ao atualizar o cliente.";
            }

            $stmt->close();
        }
    }
}

?>

<main class="main">

    <header class="topbar">

        <h1>Editar Cliente</h1>

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
                Editar cliente
            </h2>

            <p class="text-muted">
                Atualize os dados do cliente.
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
                                Nome completo *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nome"
                                name="nome"
                                maxlength="150"
                                required
                                value="<?php echo htmlspecialchars($_POST["nome"] ?? $cliente["nome"]); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="telefone" class="form-label">
                                Telefone
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="telefone"
                                name="telefone"
                                maxlength="30"
                                value="<?php echo htmlspecialchars($_POST["telefone"] ?? $cliente["telefone"] ?? ""); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="email" class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                maxlength="150"
                                value="<?php echo htmlspecialchars($_POST["email"] ?? $cliente["email"] ?? ""); ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label for="endereco" class="form-label">
                                Endereço
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="endereco"
                                name="endereco"
                                maxlength="255"
                                value="<?php echo htmlspecialchars($_POST["endereco"] ?? $cliente["endereco"] ?? ""); ?>"
                            >

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