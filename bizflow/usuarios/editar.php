<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";


$mensagem = "";

if (!isset($_GET["id"])) {

    header("Location: index.php");
    exit;

}

$id = (int) $_GET["id"];


/* ==========================================
   BUSCAR USUÁRIO
========================================== */

$sql = "
    SELECT
        id_usuario,
        nome,
        email,
        nivel,
        status
    FROM usuarios
    WHERE id_usuario = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    header("Location: index.php");
    exit;

}

$usuario = $resultado->fetch_assoc();

$stmt->close();


/* ==========================================
   ATUALIZAR USUÁRIO
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $nivel = $_POST["nivel"];
    $status = $_POST["status"];


    if (empty($nome) || empty($email)) {

        $mensagem =
            "Preencha todos os campos obrigatórios.";

    } else {

        // Verificar se outro usuário já possui este email

        $sql_verificar = "
            SELECT id_usuario
            FROM usuarios
            WHERE email = ?
            AND id_usuario != ?
        ";

        $stmt = $conn->prepare($sql_verificar);

        $stmt->bind_param(
            "si",
            $email,
            $id
        );

        $stmt->execute();

        $verificacao = $stmt->get_result();


        if ($verificacao->num_rows > 0) {

            $mensagem =
                "Este email já está sendo utilizado.";

        } else {

            if (!empty($senha)) {

                $senha_hash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $sql_atualizar = "
                    UPDATE usuarios
                    SET
                        nome = ?,
                        email = ?,
                        senha = ?,
                        nivel = ?,
                        status = ?
                    WHERE id_usuario = ?
                ";

                $stmt = $conn->prepare($sql_atualizar);

                $stmt->bind_param(
                    "sssssi",
                    $nome,
                    $email,
                    $senha_hash,
                    $nivel,
                    $status,
                    $id
                );

            } else {

                $sql_atualizar = "
                    UPDATE usuarios
                    SET
                        nome = ?,
                        email = ?,
                        nivel = ?,
                        status = ?
                    WHERE id_usuario = ?
                ";

                $stmt = $conn->prepare($sql_atualizar);

                $stmt->bind_param(
                    "ssssi",
                    $nome,
                    $email,
                    $nivel,
                    $status,
                    $id
                );

            }


            if ($stmt->execute()) {

                header("Location: index.php");
                exit;

            } else {

                $mensagem =
                    "Erro ao atualizar o usuário.";

            }

        }

    }

}

?>

<main class="main">

    <header class="topbar">

        <h1>Editar Usuário</h1>

        <div class="user-info">

            <div>

                <strong>
                    <?php
                    echo htmlspecialchars($_SESSION["nome"]);
                    ?>
                </strong>

                <small class="d-block text-muted">
                    <?php
                    echo htmlspecialchars($_SESSION["nivel"]);
                    ?>
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

            <a
                href="index.php"
                class="btn btn-outline-secondary btn-sm mb-3"
            >
                ← Voltar
            </a>

            <h2 class="fw-semibold">
                Editar usuário
            </h2>

            <p class="text-muted">
                Atualize os dados do utilizador.
            </p>

        </div>


        <?php if (!empty($mensagem)): ?>

            <div class="alert alert-warning">

                <?php
                echo htmlspecialchars($mensagem);
                ?>

            </div>

        <?php endif; ?>


        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <form method="POST">

                    <div class="row g-3">


                        <div class="col-md-6">

                            <label class="form-label">
                                Nome
                            </label>

                            <input
                                type="text"
                                name="nome"
                                class="form-control"
                                value="<?php echo htmlspecialchars($usuario["nome"]); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($usuario["email"]); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Nova senha
                            </label>

                            <input
                                type="password"
                                name="senha"
                                class="form-control"
                            >

                            <small class="text-muted">
                                Deixe em branco para manter a senha atual.
                            </small>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Nível de acesso
                            </label>

                            <select
                                name="nivel"
                                class="form-select"
                            >

                                <option
                                    value="funcionario"
                                    <?php
                                    if (
                                        $usuario["nivel"] ===
                                        "funcionario"
                                    ) echo "selected";
                                    ?>
                                >
                                    Funcionário
                                </option>

                                <option
                                    value="gerente"
                                    <?php
                                    if (
                                        $usuario["nivel"] ===
                                        "gerente"
                                    ) echo "selected";
                                    ?>
                                >
                                    Gerente
                                </option>

                                <option
                                    value="administrador"
                                    <?php
                                    if (
                                        $usuario["nivel"] ===
                                        "administrador"
                                    ) echo "selected";
                                    ?>
                                >
                                    Administrador
                                </option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="ativo"
                                    <?php
                                    if (
                                        $usuario["status"] ===
                                        "ativo"
                                    ) echo "selected";
                                    ?>
                                >
                                    Ativo
                                </option>

                                <option
                                    value="inativo"
                                    <?php
                                    if (
                                        $usuario["status"] ===
                                        "inativo"
                                    ) echo "selected";
                                    ?>
                                >
                                    Inativo
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Salvar Alterações
                        </button>

                        <a
                            href="index.php"
                            class="btn btn-outline-secondary"
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